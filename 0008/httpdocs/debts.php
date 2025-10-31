<?php
session_start();
require_once 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// ======= ОБРАБОТКА POST =======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_debt'])) {
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $due_date = $_POST['due_date'] ?? '';
        $room = trim($_POST['room'] ?? '');

        if (!$subject_id || !$description || !$due_date) {
            $error = 'Заполните все обязательные поля';
        } else {
            $stmt = $pdo->prepare("INSERT INTO debts (user_id, subject_id, description, due_date, room) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $subject_id, $description, $due_date, $room]);
            $success = 'Долг добавлен!';
        }
    }

    if (isset($_POST['complete_debt'])) {
        $debt_id = (int)($_POST['debt_id'] ?? 0);
        if ($debt_id) {
            $stmt = $pdo->prepare("UPDATE debts SET is_completed = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$debt_id, $user_id]);
            $success = 'Долг отмечен как выполненный!';
        }
    }

    if (isset($_POST['delete_debt'])) {
        $debt_id = (int)($_POST['debt_id'] ?? 0);
        if ($debt_id) {
            $stmt = $pdo->prepare("DELETE FROM debts WHERE id = ? AND user_id = ?");
            $stmt->execute([$debt_id, $user_id]);
            $success = 'Долг удален!';
        }
    }
}

// ======= ПОЛУЧЕНИЕ ДАННЫХ =======
$subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll();
$debts_stmt = $pdo->prepare("
    SELECT d.*, s.name as subject_name,
           CASE WHEN d.due_date < CURDATE() THEN 1 ELSE 0 END as is_overdue
    FROM debts d
    JOIN subjects s ON d.subject_id = s.id
    WHERE d.user_id = ? AND d.is_completed = 0
    ORDER BY d.due_date, s.name
");
$debts_stmt->execute([$user_id]);
$debts = $debts_stmt->fetchAll();

$pageTitle = 'Долги - Student Dark Notebook';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">🧾 Мои долги</h2>

    <?php if ($success): ?><div class="success-message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card add-debt-form">
        <h3>Добавить долг</h3>
        <form method="POST" class="grade-form">
            <select name="subject_id" required>
                <option value="">Выберите предмет</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="description" placeholder="Что сдать" required>
            <input type="date" name="due_date" required>
            <input type="text" name="room" placeholder="Кабинет">
            <button type="submit" name="add_debt" class="btn-primary btn-sm">Добавить</button>
        </form>
    </div>

    <div class="grades-container">
        <?php if (!$debts): ?>
            <div class="card"><p>✅ У вас нет долгов!</p></div>
        <?php else: ?>
            <?php foreach ($debts as $d): ?>
                <div class="grade-card <?= $d['is_overdue'] ? 'overdue' : '' ?>">
                    <h3 class="grade-subject"><?= htmlspecialchars($d['subject_name']) ?></h3>
                    <p><?= htmlspecialchars($d['description']) ?></p>
                    <p>📅 <?= date('d.m.Y', strtotime($d['due_date'])) ?> | 🚪 <?= htmlspecialchars($d['room']) ?></p>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="debt_id" value="<?= $d['id'] ?>">
                        <button type="submit" name="complete_debt" class="btn-primary btn-sm">✓ Выполнено</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="debt_id" value="<?= $d['id'] ?>">
                        <button type="submit" name="delete_debt" class="btn-danger btn-sm">✕ Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
