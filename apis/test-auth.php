<?php
// apis/test-auth.php
// Debug endpoint to test authentication header
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\JwtAuth;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Debug: Show all headers
$debug = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers_received' => []
];

// Check different header variations
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $debug['headers_received']['HTTP_AUTHORIZATION'] = substr($_SERVER['HTTP_AUTHORIZATION'], 0, 50) . '...';
}
if (!empty($_SERVER['Authorization'])) {
    $debug['headers_received']['Authorization'] = substr($_SERVER['Authorization'], 0, 50) . '...';
}

if (function_exists('getallheaders')) {
    $all = getallheaders();
    foreach ($all as $name => $value) {
        if (stripos($name, 'authorization') !== false) {
            $debug['all_headers'][$name] = substr($value, 0, 50) . '...';
        }
    }
}

// Try to verify token
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_NAME'] ?? 'shop'
    );
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $auth = new JwtAuth($pdo);
    
    // Get header
    $header = '';
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['Authorization'])) {
        $header = $_SERVER['Authorization'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $header = $headers['Authorization'];
        }
    }

    if (!$header) {
        $debug['error'] = 'No Authorization header found';
    } else {
        $debug['header_found'] = true;
        
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            $token = trim($matches[1]);
            $debug['token_extracted'] = true;
            $debug['token_length'] = strlen($token);
            
            $payload = $auth->verify($token);
            if ($payload) {
                $debug['token_verified'] = true;
                $debug['user_id'] = $payload->sub;
                $debug['username'] = $payload->username;
            } else {
                $debug['token_verified'] = false;
                $debug['error'] = 'Token verification failed';
            }
        } else {
            $debug['error'] = 'Bearer token not found in header';
        }
    }

} catch (Exception $e) {
    $debug['exception'] = $e->getMessage();
}

http_response_code(200);
echo json_encode($debug);
