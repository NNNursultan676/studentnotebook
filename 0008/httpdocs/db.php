<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'nursulta_db';
$user = 'nursulta';
$pass = '72416810'; // пароль, который указан в Plesk


$csrfPaths = [
    __DIR__ . '/includes/csrf.php',
    __DIR__ . '/../includes/csrf.php',
    __DIR__ . '/app/includes/csrf.php',
];

foreach ($csrfPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "✅ Подключение к базе успешно<br>";
} catch (PDOException $e) {
    echo "❌ Ошибка подключения: " . $e->getMessage();
}

// Сессия
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $stmt = $pdo->query("SELECT NOW()");
    echo "<!-- DB connected OK -->";
} catch (Exception $e) {
    echo "<!-- DB connection error: " . $e->getMessage() . " -->";
}

?>
