<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Calculate total points for all users
$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name,
           SUM((g.rk1 + g.rk2) * 0.6 + (g.exam_score / g.exam_max * 100) * 0.4) as total_points
    FROM users u
    LEFT JOIN grades g ON u.id = g.user_id
    WHERE u.role = 'student'
    GROUP BY u.id, u.username, u.full_name
    ORDER BY total_points DESC
");
$ratings = $stmt->fetchAll();

$pageTitle = 'Рейтинг - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">🏆 Рейтинг студентов</h2>
    
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
                foreach ($ratings as $rating): 
                    $is_current_user = ($rating['id'] == $_SESSION['user_id']);
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
                            <?php echo htmlspecialchars($rating['full_name'] ?: $rating['username']); ?>
                            <?php if ($is_current_user): ?>
                                <span class="badge">Вы</span>
                            <?php endif; ?>
                        </td>
                        <td class="points"><?php echo number_format($rating['total_points'] ?? 0, 1); ?></td>
                    </tr>
                <?php 
                    $position++;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
