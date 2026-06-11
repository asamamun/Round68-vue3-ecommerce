<?php
// backend/bootstrap.php
foreach (file(__DIR__ . '/.env') as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;
    [$key, $val] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($val);
}