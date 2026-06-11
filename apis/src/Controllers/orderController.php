<?php
// src/Controllers/OrderController.php

namespace App\Controllers;

use App\Auth\JwtAuth;
use PDO;

class OrderController
{
    public function __construct(
        private PDO     $db,
        private JwtAuth $auth
    ) {}

    // POST /orders  — requires Authorization: Bearer <access_token>
    public function create(): void
    {
        $payload = $this->requireAuth();

        $body  = json_decode(file_get_contents('php://input'), true);
        $items = $body['items'] ?? [];

        if (empty($items)) {
            http_response_code(422);
            echo json_encode(['error' => 'Cart is empty']);
            return;
        }

        // Validate & sanitise each item coming from the Vue cart store
        $clean = [];
        foreach ($items as $item) {
            $clean[] = [
                'id'        => (int)   $item['product']['id'],
                'title'     => (string)$item['product']['title'],
                'price'     => (float) $item['product']['price'],
                'quantity'  => (int)   $item['quantity'],
                'thumbnail' => (string)$item['product']['thumbnail'],
            ];
        }

        $totalItems = array_sum(array_column($clean, 'quantity'));
        $totalPrice = array_sum(
            array_map(fn($i) => $i['price'] * $i['quantity'], $clean)
        );

        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, items, total_items, total_price)
             VALUES (:uid, :items, :ti, :tp)'
        );
        $stmt->execute([
            ':uid'   => $payload->sub,
            ':items' => json_encode($clean),
            ':ti'    => $totalItems,
            ':tp'    => round($totalPrice, 2),
        ]);

        http_response_code(201);
        echo json_encode([
            'order_id'    => (int)$this->db->lastInsertId(),
            'total_items' => $totalItems,
            'total_price' => round($totalPrice, 2),
        ]);
    }

    // PUT /orders/{id}  — update order status (admin only)
    public function updateStatus(): void
    {
        $payload = $this->requireAuth();

        // Only admins can update order status
        if ($payload->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: Admin access required']);
            exit;
        }

        $body   = json_decode(file_get_contents('php://input'), true);
        $status = trim($body['status'] ?? '');
        $orderId = (int)($body['order_id'] ?? 0);

        if (!$orderId) {
            http_response_code(422);
            echo json_encode(['error' => 'Order ID is required']);
            exit;
        }

        // Validate status
        $validStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        // Update order status
        $stmt = $this->db->prepare(
            'UPDATE orders
                SET status = :status
              WHERE id = :id'
        );
        $stmt->execute([
            ':status' => $status,
            ':id'     => $orderId,
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'message' => 'Order status updated successfully',
            'order_id' => $orderId,
            'status' => $status,
        ]);
    }

    // GET /orders  — returns the logged-in user's order history, or all orders if admin
    public function index(): void
    {
        $payload = $this->requireAuth();

        // If admin, fetch all orders; otherwise fetch only user's orders
        if ($payload->role === 'admin') {
            $stmt = $this->db->prepare(
                'SELECT o.id, o.user_id, u.username, u.email, o.items, o.total_items, 
                        o.total_price, o.status, o.created_at
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   ORDER BY o.created_at DESC'
            );
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare(
                'SELECT id, user_id, items, total_items, total_price, status, created_at
                   FROM orders
                  WHERE user_id = :uid
                  ORDER BY created_at DESC'
            );
            $stmt->execute([':uid' => $payload->sub]);
        }
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode the JSON items column
        foreach ($orders as &$o) {
            $o['items'] = json_decode($o['items'], true);
        }

        echo json_encode($orders);
    }

    // ----------------------------------------------------------
    private function requireAuth(): object
    {
        // Try multiple header variations (PHP converts headers differently)
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
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorised: No Authorization header provided']);
            exit;
        }

        // Extract token from "Bearer <token>" format
        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorised: Invalid Authorization header format']);
            exit;
        }

        $token = trim($matches[1]);
        $payload = $this->auth->verify($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorised: Invalid or expired token']);
            exit;
        }

        return $payload;
    }
}