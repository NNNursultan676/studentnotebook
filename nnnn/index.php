<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_date = $_GET['date'] ?? date('Y-m-d');

// Get schedule for the selected date
$stmt = $pdo->prepare("
    SELECT s.*, sub.name as subject_name, sub.teacher_phone
    FROM schedule s
    JOIN subjects sub ON s.subject_id = sub.id
    WHERE s.date = ?
    ORDER BY s.time
");
$stmt->execute([$current_date]);
$schedule = $stmt->fetchAll();

// Previous and next day
$prev_date = date('Y-m-d', strtotime($current_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($current_date . ' +1 day'));

$pageTitle = 'Главная - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <div class="date-navigation">
        <a href="?date=<?php echo $prev_date; ?>" class="date-nav-btn">←</a>
        <h2 class="current-date"><?php echo date('d.m.Y', strtotime($current_date)); ?></h2>
        <a href="?date=<?php echo $next_date; ?>" class="date-nav-btn">→</a>
    </div>

    <div class="schedule-section">
        <h3 class="section-title">Расписание</h3>
        
        <?php if (empty($schedule)): ?>
            <div class="empty-state">
                <p>📅 На этот день нет занятий</p>
            </div>
        <?php else: ?>
            <div class="schedule-grid">
                <?php foreach ($schedule as $class): ?>
                    <div class="schedule-card">
                        <div class="schedule-time"><?php echo htmlspecialchars($class['time']); ?></div>
                        <div class="schedule-subject"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                        <div class="schedule-details">
                            <span>🚪 <?php echo htmlspecialchars($class['room']); ?></span>
                            <span>👨‍🏫 <?php echo htmlspecialchars($class['teacher']); ?></span>
                            <?php if (!empty($class['teacher_phone'])): ?>
                                <span>📱 <?php echo htmlspecialchars($class['teacher_phone']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
