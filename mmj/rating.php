<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$selected_subject = $_GET['subject'] ?? 'all';

// Get all subjects
$subjects_stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name");
$subjects = $subjects_stmt->fetchAll();

// Get all students with their grades
if ($selected_subject === 'all') {
    // Show summary for all subjects
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.full_name,
               SUM((g.rk1 + g.rk2) * 0.6 + (g.exam_score / g.exam_max * 100) * 0.4) as total_points
        FROM users u
        LEFT JOIN grades g ON u.id = g.user_id
        WHERE u.role = 'student'
        GROUP BY u.id, u.username, u.full_name
        ORDER BY total_points DESC
    ");
    $students_data = $stmt->fetchAll();
} else {
    // Show grades for specific subject
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name,
               g.rk1, g.rk2, g.exam_score, g.exam_max,
               (g.rk1 + g.rk2) * 0.6 + (g.exam_score / g.exam_max * 100) * 0.4 as total
        FROM users u
        LEFT JOIN grades g ON u.id = g.user_id AND g.subject_id = ?
        WHERE u.role = 'student'
        ORDER BY (total IS NULL), total DESC, u.full_name
    ");
    $stmt->execute([$selected_subject]);
    $students_data = $stmt->fetchAll();
    
    // Get subject name
    $subject_name_stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
    $subject_name_stmt->execute([$selected_subject]);
    $subject_info = $subject_name_stmt->fetch();
}

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$pageTitle = 'Журнал оценок - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">📖 Журнал оценок</h2>
    
    <div class="journal-controls">
        <div class="subject-selector">
            <label for="subject-select">Выбрать предмет:</label>
            <select id="subject-select" onchange="window.location.href='rating.php?subject=' + this.value" class="subject-dropdown">
                <option value="all" <?php echo $selected_subject === 'all' ? 'selected' : ''; ?>>📊 Общий рейтинг</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo $subject['id']; ?>" <?php echo $selected_subject == $subject['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if ($selected_subject === 'all'): ?>
        <!-- Overall Rating View -->
        <div class="rating-info">
            <p>Рейтинг рассчитывается на основе суммы всех баллов по предметам</p>
        </div>

        <div class="rating-table">
            <table>
                <thead>
                    <tr>
                        <th>Место</th>
                        <th>Студент</th>
                        <th>Всего баллов</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $position = 1;
                    foreach ($students_data as $student): 
                        $is_current_user = ($student['id'] == $_SESSION['user_id']);
                    ?>
                        <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                            <td class="position">
                                <?php 
                                if ($position == 1) echo '🥇';
                                elseif ($position == 2) echo '🥈';
                                elseif ($position == 3) echo '🥉';
                                else echo $position;
                                ?>
                            </td>
                            <td class="student-name">
                                <?php echo htmlspecialchars($student['full_name'] ?: $student['username']); ?>
                                <?php if ($is_current_user): ?>
                                    <span class="badge">Вы</span>
                                <?php endif; ?>
                            </td>
                            <td class="points"><?php echo number_format($student['total_points'] ?? 0, 1); ?></td>
                        </tr>
                    <?php 
                        $position++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Subject-specific grades view -->
        <div class="rating-info">
            <p><strong>Предмет:</strong> <?php echo htmlspecialchars($subject_info['name'] ?? ''); ?></p>
            <p>Формула: (РК1 + РК2) × 0.6 + Экзамен% × 0.4</p>
        </div>

        <div class="journal-table">
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Студент</th>
                        <th>РК1</th>
                        <th>РК2</th>
                        <th>Экзамен</th>
                        <th>Итог</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $position = 1;
                    foreach ($students_data as $student): 
                        $is_current_user = ($student['id'] == $_SESSION['user_id']);
                        $rk1 = $student['rk1'] ?? 0;
                        $rk2 = $student['rk2'] ?? 0;
                        $exam = $student['exam_score'] ?? 0;
                        $exam_max = $student['exam_max'] ?? 100;
                        $total = $student['total'] ?? 0;
                    ?>
                        <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                            <td class="position-num"><?php echo $position; ?></td>
                            <td class="student-name">
                                <?php echo htmlspecialchars($student['full_name'] ?: $student['username']); ?>
                                <?php if ($is_current_user): ?>
                                    <span class="badge">Вы</span>
                                <?php endif; ?>
                            </td>
                            <td class="grade-cell"><?php echo number_format($rk1, 1); ?></td>
                            <td class="grade-cell"><?php echo number_format($rk2, 1); ?></td>
                            <td class="grade-cell"><?php echo number_format($exam, 1); ?> / <?php echo $exam_max; ?></td>
                            <td class="grade-total"><?php echo number_format($total, 1); ?></td>
                        </tr>
                    <?php 
                        $position++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
