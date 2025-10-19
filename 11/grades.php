<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle grade update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    check_csrf();
    
    $grade_id = $_POST['grade_id'] ?? 0;
    $rk1 = floatval($_POST['rk1'] ?? 0);
    $rk2 = floatval($_POST['rk2'] ?? 0);
    $exam_score = floatval($_POST['exam_score'] ?? 0);
    
    $stmt = $pdo->prepare("UPDATE grades SET rk1 = ?, rk2 = ?, exam_score = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$rk1, $rk2, $exam_score, $grade_id, $user_id])) {
        $success = 'Оценки обновлены!';
    } else {
        $error = 'Ошибка обновления';
    }
}

// Get all subjects and user's grades
$stmt = $pdo->prepare("
    SELECT s.id as subject_id, s.name, s.teacher, s.max_points,
           g.id as grade_id, g.rk1, g.rk2, g.exam_score, g.exam_max
    FROM subjects s
    LEFT JOIN grades g ON s.id = g.subject_id AND g.user_id = ?
    ORDER BY s.name
");
$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll();

// Create grades for subjects that don't have them yet
foreach ($subjects as &$subject) {
    if (!$subject['grade_id']) {
        $stmt = $pdo->prepare("INSERT INTO grades (user_id, subject_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $subject['subject_id']]);
        $subject['grade_id'] = $pdo->lastInsertId();
        $subject['rk1'] = 0;
        $subject['rk2'] = 0;
        $subject['exam_score'] = 0;
        $subject['exam_max'] = 100;
    }
}

$pageTitle = 'Оценки - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">📓 Мои оценки</h2>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="grades-container">
        <?php foreach ($subjects as $subject): ?>
            <div class="grade-card">
                <h3 class="grade-subject"><?php echo htmlspecialchars($subject['name']); ?></h3>
                <p class="grade-teacher">👨‍🏫 <?php echo htmlspecialchars($subject['teacher']); ?></p>
                
                <form method="POST" class="grade-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="grade_id" value="<?php echo $subject['grade_id']; ?>">
                    
                    <div class="grade-inputs">
                        <div class="grade-input-group">
                            <label>РК1</label>
                            <input type="number" name="rk1" step="0.1" min="0" max="100" 
                                   value="<?php echo $subject['rk1']; ?>">
                        </div>
                        
                        <div class="grade-input-group">
                            <label>РК2</label>
                            <input type="number" name="rk2" step="0.1" min="0" max="100" 
                                   value="<?php echo $subject['rk2']; ?>">
                        </div>
                        
                        <div class="grade-input-group">
                            <label>Экзамен</label>
                            <input type="number" name="exam_score" step="0.1" min="0" max="<?php echo $subject['exam_max']; ?>" 
                                   value="<?php echo $subject['exam_score']; ?>">
                        </div>
                    </div>
                    
                    <?php 
                    $total = ($subject['rk1'] + $subject['rk2']) * 0.6 + ($subject['exam_score'] / $subject['exam_max'] * 100) * 0.4;
                    ?>
                    <div class="grade-total">
                        <strong>Итог:</strong> <?php echo number_format($total, 1); ?>
                    </div>
                    
                    <button type="submit" name="update_grade" class="btn-primary btn-sm">Сохранить</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
