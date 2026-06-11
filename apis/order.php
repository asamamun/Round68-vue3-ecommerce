<?php
// apis/order.php
// Handles POST (create order) and GET (list user's orders)
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\OrderController;
use App\Auth\JwtAuth;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

try {
    // ── Instantiate controller ────────────────────────────────────
    $auth       = new JwtAuth($pdo);
    $controller = new OrderController($pdo, $auth);

    // ── Route requests ───────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->create();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->index();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $controller->updateStatus();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
