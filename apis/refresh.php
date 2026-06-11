<?php
// apis/refresh.php
// Refresh access token using refresh token
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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// ── Parse body ────────────────────────────────────────────────
$body          = json_decode(file_get_contents('php://input'), true);
$refreshToken  = trim($body['refresh_token'] ?? '');

if (!$refreshToken) {
    http_response_code(422);
    echo json_encode(['error' => 'Refresh token is required']);
    exit;
}

// ── Attempt token refresh ─────────────────────────────────────
$auth   = new JwtAuth($pdo);
$result = $auth->refresh($refreshToken);

if (!$result) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired refresh token']);
    exit;
}

http_response_code(200);
echo json_encode($result);
