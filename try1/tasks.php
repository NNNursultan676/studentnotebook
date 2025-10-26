<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle task completion toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_task'])) {
    $task_id = $_POST['task_id'] ?? 0;
    
    // Check if completion exists
    $stmt = $pdo->prepare("SELECT id, is_done FROM task_completions WHERE user_id = ? AND task_id = ?");
    $stmt->execute([$user_id, $task_id]);
    $completion = $stmt->fetch();
    
    if ($completion) {
        $new_status = !$completion['is_done'];
        $stmt = $pdo->prepare("UPDATE task_completions SET is_done = ?, completed_at = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $new_status ? date('Y-m-d H:i:s') : null, $completion['id']])) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: tasks.php?nocache=' . uniqid());
            exit;
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO task_completions (user_id, task_id, is_done, completed_at) VALUES (?, ?, 1, ?)");
        if ($stmt->execute([$user_id, $task_id, date('Y-m-d H:i:s')])) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: tasks.php?nocache=' . uniqid());
            exit;
        }
    }
}

// Get all tasks for the current week
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare("
    SELECT t.*, s.name as subject_name,
           tc.is_done,
           CASE WHEN t.due_date < CURDATE() AND (tc.is_done = 0 OR tc.is_done IS NULL) THEN 1 ELSE 0 END as is_overdue
    FROM tasks t
    JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN task_completions tc ON t.id = tc.task_id AND tc.user_id = ?
    WHERE t.due_date BETWEEN ? AND ?
    ORDER BY t.due_date, s.name
");
$stmt->execute([$user_id, $week_start, $week_end]);
$tasks = $stmt->fetchAll();

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$pageTitle = 'Задания - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">📚 Задания и домашка</h2>
    
    <div class="week-info">
        <p>Неделя: <?php echo date('d.m', strtotime($week_start)); ?> - <?php echo date('d.m.Y', strtotime($week_end)); ?></p>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <p>📭 На эту неделю заданий нет</p>
        </div>
    <?php else: ?>
        <div class="tasks-list">
            <?php foreach ($tasks as $task): ?>
                <div class="task-card <?php echo $task['is_done'] ? 'completed' : ''; ?> <?php echo $task['is_overdue'] ? 'overdue' : ''; ?>">
                    <div class="task-header">
                        <form method="POST" class="task-checkbox-form">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="checkbox" name="toggle_task" 
                                   <?php echo $task['is_done'] ? 'checked' : ''; ?>
                                   onchange="this.form.submit()">
                        </form>
                        <div class="task-info">
                            <h4 class="task-subject"><?php echo htmlspecialchars($task['subject_name']); ?></h4>
                            <p class="task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($task['description']): ?>
                        <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="task-footer">
                        <span class="task-due">📅 <?php echo date('d.m.Y', strtotime($task['due_date'])); ?></span>
                        <?php if ($task['points'] > 0): ?>
                            <span class="task-points">⭐ <?php echo $task['points']; ?> баллов</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
