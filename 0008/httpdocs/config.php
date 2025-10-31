<?php
// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'nursulta');
define('DB_PASS', '72416810');
define('DB_NAME', 'nursulta_db');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("❌ Ошибка подключения к базе данных: " . $e->getMessage());
}

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
