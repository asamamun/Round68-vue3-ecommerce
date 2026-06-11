<?php
// backend/register.php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\JwtAuth;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── DB connection ─────────────────────────────────────────────
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_NAME'] ?? 'shop'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    // ── Parse & validate body ─────────────────────────────────────
    $body     = json_decode(file_get_contents('php://input'), true);
    $username = trim($body['username'] ?? '');
    $email    = trim($body['email']    ?? '');
    $password = trim($body['password'] ?? '');

    $errors = [];

    if (!$username || strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address';
    }
    if (!$password || strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if ($errors) {
        http_response_code(422);
        echo json_encode(['errors' => $errors]);
        exit;
    }

    // ── Check uniqueness ──────────────────────────────────────────
    $stmt = $pdo->prepare(
        'SELECT
            SUM(username = :u) AS taken_username,
            SUM(email    = :e) AS taken_email
         FROM users'
    );
    $stmt->execute([':u' => $username, ':e' => $email]);
    $taken = $stmt->fetch();

    if ($taken['taken_username']) {
        http_response_code(409);
        echo json_encode(['errors' => ['username' => 'Username already taken']]);
        exit;
    }
    if ($taken['taken_email']) {
        http_response_code(409);
        echo json_encode(['errors' => ['email' => 'Email already registered']]);
        exit;
    }

    // ── Insert user ───────────────────────────────────────────────
    $hash = password_hash($password, PASSWORD_ARGON2ID);   // falls back to bcrypt if argon2 unavailable

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash)
         VALUES (:u, :e, :h)'
    );
    $stmt->execute([':u' => $username, ':e' => $email, ':h' => $hash]);
    $userId = (int) $pdo->lastInsertId();

    // ── Auto-login after registration ─────────────────────────────
    $auth   = new JwtAuth($pdo);
    $result = $auth->login($username, $password);

    http_response_code(201);
    echo json_encode(array_merge(
        ['message' => 'Account created successfully'],
        $result  // includes access_token, refresh_token, user {}
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    exit;
}