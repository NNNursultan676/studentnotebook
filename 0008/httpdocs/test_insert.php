<?php
require 'db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO debts (user_id, subject_id, description, due_date, room) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([1, 1, 'Тестовая запись PHP', '2025-12-31', '101']);
    echo "✅ Запись успешно добавлена!";
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
