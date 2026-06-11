<?php
// src/Auth/JwtAuth.php
// Requires: composer require firebase/php-jwt

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class JwtAuth
{
    private string $accessSecret;
    private string $refreshSecret;
    private int    $accessTtl  = 900;       // 15 min
    private int    $refreshTtl = 604800;    // 7 days

    public function __construct(private PDO $db)
    {
        $this->accessSecret  = $_ENV['JWT_ACCESS_SECRET'] ?? '';
        $this->refreshSecret = $_ENV['JWT_REFRESH_SECRET'] ?? '';
        
        if (!$this->accessSecret || !$this->refreshSecret) {
            throw new \Exception('JWT secrets not configured in .env');
        }
    }

    // ----------------------------------------------------------
    //  LOGIN  →  returns [access_token, refresh_token] or null
    // ----------------------------------------------------------
    public function login(string $username, string $password): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, password_hash, role
               FROM users
              WHERE username = :u AND is_active = 1
              LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        $accessToken  = $this->issueAccessToken($user);
        $refreshToken = $this->issueRefreshToken($user['id']);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->accessTtl,
            'user' => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ],
        ];
    }

    // ----------------------------------------------------------
    //  REFRESH  →  rotates both tokens
    // ----------------------------------------------------------
    public function refresh(string $refreshToken): ?array
    {
        try {
            $payload = JWT::decode($refreshToken, new Key($this->refreshSecret, 'HS256'));
        } catch (\Throwable) {
            return null;
        }

        $hashed = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare(
            'SELECT id, username, email, role
               FROM users
              WHERE id = :id
                AND refresh_token    = :token
                AND token_expires_at > NOW()
                AND is_active        = 1
              LIMIT 1'
        );
        $stmt->execute([':id' => $payload->sub, ':token' => $hashed]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return null;

        $newAccess  = $this->issueAccessToken($user);
        $newRefresh = $this->issueRefreshToken($user['id']);   // rotate

        return [
            'access_token'  => $newAccess,
            'refresh_token' => $newRefresh,
            'expires_in'    => $this->accessTtl,
        ];
    }

    // ----------------------------------------------------------
    //  VERIFY  →  call this in every protected endpoint
    // ----------------------------------------------------------
    public function verify(string $accessToken): ?object
    {
        try {
            return JWT::decode($accessToken, new Key($this->accessSecret, 'HS256'));
        } catch (\Throwable) {
            return null;
        }
    }

    // ----------------------------------------------------------
    //  LOGOUT  →  invalidates the refresh token in the DB
    // ----------------------------------------------------------
    public function logout(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
                SET refresh_token    = NULL,
                    token_expires_at = NULL
              WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
    }

    // ----------------------------------------------------------
    //  HELPERS
    // ----------------------------------------------------------
    private function issueAccessToken(array $user): string
    {
        $now = time();
        return JWT::encode([
            'iss' => $_ENV['APP_URL'],
            'sub' => $user['id'],
            'iat' => $now,
            'exp' => $now + $this->accessTtl,
            'username' => $user['username'],
            'role' => $user['role'],
        ], $this->accessSecret, 'HS256');
    }

    private function issueRefreshToken(int $userId): string
    {
        $now   = time();
        $token = JWT::encode([
            'sub' => $userId,
            'iat' => $now,
            'exp' => $now + $this->refreshTtl,
        ], $this->refreshSecret, 'HS256');

        // Store hashed token in DB (refresh token rotation)
        $stmt = $this->db->prepare(
            'UPDATE users
                SET refresh_token    = :token,
                    token_expires_at = DATE_ADD(NOW(), INTERVAL :ttl SECOND)
              WHERE id = :id'
        );
        $stmt->execute([
            ':token' => hash('sha256', $token),
            ':ttl'   => $this->refreshTtl,
            ':id'    => $userId,
        ]);

        return $token;
    }
}
