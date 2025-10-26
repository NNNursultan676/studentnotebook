<?php
// Database configuration for MySQL
// For local development or Plesk hosting

$host = getenv('DB_HOST') ?: 'localhost:3306';
$user = getenv('DB_USER') ?: 'nurstry2';
$pass = getenv('DB_PASS') ?: '72416810Nurs';
$db   = getenv('DB_NAME') ?: 'nursulta_db2';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $e) {
  // Redirect to setup page if database is not configured
  if (basename($_SERVER['PHP_SELF']) !== 'setup_info.php' && basename($_SERVER['PHP_SELF']) !== 'demo.php') {
    header('Location: /setup_info.php');
    exit;
  }
  $pdo = null;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_strict_mode', 1);
  session_start();
}

// Include CSRF protection
if (file_exists(__DIR__ . '/includes/csrf.php')) {
  require_once __DIR__ . '/includes/csrf.php';
}
?>
