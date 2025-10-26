<?php
// Показываем ошибки для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'nurstry2';
$pass = getenv('DB_PASS') ?: '72416810Nurs';
$db   = getenv('DB_NAME') ?: 'nursulta_db2';

try {
  $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $e) {
  die("❌ Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_strict_mode', 1);
  session_start();
}
?>
