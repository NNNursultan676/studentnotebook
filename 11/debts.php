<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle new debt addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_debt'])) {
    check_csrf();
    
    $subject_id = $_POST['subject_id'] ?? 0;
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $room = trim($_POST['room'] ?? '');
    
    if (empty($description) || empty($due_date)) {
        $error = 'Заполните все обязательные поля';
    } else {
        $stmt = $pdo->prepare("INSERT INTO debts (user_id, subject_id, description, due_date, room) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $subject_id, $description, $due_date, $room])) {
            $success = 'Долг добавлен!';
        } else {
            $error = 'Ошибка добавления';
        }
    }
}

// Handle debt completion
if (isset($_POST['complete_debt'])) {
    check_csrf();
    
    $debt_id = $_POST['debt_id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE debts SET is_completed = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$debt_id, $user_id]);
}

// Handle debt deletion
if (isset($_POST['delete_debt'])) {
    check_csrf();
    
    $debt_id = $_POST['debt_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM debts WHERE id = ? AND user_id = ?");
    $stmt->execute([$debt_id, $user_id]);
}

// Get all subjects
$stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
$subjects = $stmt->fetchAll();

// Get user's debts
$stmt = $pdo->prepare("
    SELECT d.*, s.name as subject_name,
           CASE WHEN d.due_date < CURDATE() THEN 1 ELSE 0 END as is_overdue
    FROM debts d
    JOIN subjects s ON d.subject_id = s.id
    WHERE d.user_id = ? AND d.is_completed = 0
    ORDER BY d.due_date, s.name
");
$stmt->execute([$user_id]);
$debts = $stmt->fetchAll();

$pageTitle = 'Долги - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">🧾 Мои долги</h2>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="add-debt-form">
        <h3>Добавить долг</h3>
        <form method="POST" class="form-inline">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="subject_id">Предмет</label>
                    <select name="subject_id" id="subject_id" required>
                        <option value="">Выберите предмет</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Что сдать</label>
                    <input type="text" name="description" id="description" required>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Когда</label>
                    <input type="date" name="due_date" id="due_date" required>
                </div>
                
                <div class="form-group">
                    <label for="room">Кабинет</label>
                    <input type="text" name="room" id="room">
                </div>
            </div>
            
            <button type="submit" name="add_debt" class="btn-primary">Добавить</button>
        </form>
    </div>

    <div class="debts-list">
        <?php if (empty($debts)): ?>
            <div class="empty-state">
                <p>✅ У вас нет долгов!</p>
            </div>
        <?php else: ?>
            <?php foreach ($debts as $debt): ?>
                <div class="debt-card <?php echo $debt['is_overdue'] ? 'overdue' : ''; ?>">
                    <div class="debt-header">
                        <h4 class="debt-subject"><?php echo htmlspecialchars($debt['subject_name']); ?></h4>
                        <div class="debt-actions">
                            <form method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="debt_id" value="<?php echo $debt['id']; ?>">
                                <button type="submit" name="complete_debt" class="btn-icon" title="Отметить как выполненное">✓</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="debt_id" value="<?php echo $debt['id']; ?>">
                                <button type="submit" name="delete_debt" class="btn-icon btn-danger" title="Удалить">✕</button>
                            </form>
                        </div>
                    </div>
                    
                    <p class="debt-description"><?php echo htmlspecialchars($debt['description']); ?></p>
                    
                    <div class="debt-footer">
                        <span class="debt-due">📅 <?php echo date('d.m.Y', strtotime($debt['due_date'])); ?></span>
                        <?php if ($debt['room']): ?>
                            <span class="debt-room">🚪 <?php echo htmlspecialchars($debt['room']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
