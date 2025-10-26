<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$desired_score = $_POST['desired_score'] ?? 90;
$recommendations = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all subjects and current grades
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, g.rk1, g.rk2, g.exam_score, g.exam_max
        FROM subjects s
        LEFT JOIN grades g ON s.id = g.subject_id AND g.user_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$user_id]);
    $subjects = $stmt->fetchAll();
    
    foreach ($subjects as $subject) {
        $rk1 = $subject['rk1'] ?? 0;
        $rk2 = $subject['rk2'] ?? 0;
        $exam_score = $subject['exam_score'] ?? 0;
        $exam_max = $subject['exam_max'] ?? 100;
        
        // Calculate current total
        $current_total = ($rk1 + $rk2) * 0.6 + ($exam_score / $exam_max * 100) * 0.4;
        
        // Formula: итог = (РК1 + РК2) * 0.6 + (Экзамен%) * 0.4
        
        $advice = '';
        $needed_rk2 = 0;
        $needed_exam_percent = 0;
        
        if ($current_total >= $desired_score) {
            // Already achieved the target
            $advice = "✅ Вы уже достигли целевого балла! Текущий: " . number_format($current_total, 1);
        } else {
            // Calculate what's needed
            
            if ($rk1 == 0 && $rk2 == 0) {
                // No RK scores yet - calculate ideal distribution
                // Try to maximize RK scores first (they're worth more together)
                
                // If we max out both RKs (200 points total * 0.6 = 120 points)
                $max_rk_contribution = 200 * 0.6;
                
                if ($max_rk_contribution >= $desired_score) {
                    // Can achieve with RKs alone
                    $total_rk_needed = $desired_score / 0.6;
                    $needed_rk1 = min(100, $total_rk_needed / 2);
                    $needed_rk2 = min(100, $total_rk_needed / 2);
                    $needed_exam_percent = 0;
                    
                    $advice = sprintf(
                        "📝 РК1: %.1f балла | РК2: %.1f балла | 📄 Экзамен: %.1f%%",
                        $needed_rk1,
                        $needed_rk2,
                        $needed_exam_percent
                    );
                } else {
                    // Need max RKs + exam
                    $needed_rk1 = 100;
                    $needed_rk2 = 100;
                    $needed_exam_percent = ($desired_score - $max_rk_contribution) / 0.4;
                    
                    if ($needed_exam_percent > 100) {
                        $advice = "⚠️ Целевой балл недостижим (макс. возможный: 160)";
                    } else {
                        $advice = sprintf(
                            "📝 РК1: %.1f балла | РК2: %.1f балла | 📄 Экзамен: %.1f%%",
                            $needed_rk1,
                            $needed_rk2,
                            $needed_exam_percent
                        );
                    }
                }
            } elseif ($rk1 > 0 && $rk2 == 0) {
                // Only RK1 is done - calculate needed RK2 and exam
                
                // Try to get target with max RK2
                $needed_rk2 = 100;
                $current_rk = ($rk1 + $needed_rk2) * 0.6;
                $needed_exam_percent = ($desired_score - $current_rk) / 0.4;
                
                if ($needed_exam_percent > 100) {
                    // Need to check what's actually possible
                    $max_possible = ($rk1 + 100) * 0.6 + 100 * 0.4;
                    if ($max_possible < $desired_score) {
                        $advice = sprintf(
                            "⚠️ Целевой балл недостижим с РК1=%.1f (макс. возможный: %.1f)",
                            $rk1,
                            $max_possible
                        );
                    } else {
                        // Adjust RK2 down, max exam
                        $needed_exam_percent = 100;
                        $needed_rk2 = ($desired_score - 40) / 0.6 - $rk1;
                        
                        if ($needed_rk2 < 0) {
                            $needed_rk2 = 0;
                        }
                        
                        $advice = sprintf(
                            "📝 РК2: %.1f балла | 📄 Экзамен: %.1f%%",
                            $needed_rk2,
                            $needed_exam_percent
                        );
                    }
                } else {
                    $advice = sprintf(
                        "📝 РК2: %.1f балла | 📄 Экзамен: %.1f%%",
                        max(0, $needed_rk2),
                        max(0, $needed_exam_percent)
                    );
                }
            } else {
                // Both RKs are done - only exam left
                $current_rk = ($rk1 + $rk2) * 0.6;
                $needed_exam_percent = ($desired_score - $current_rk) / 0.4;
                
                if ($needed_exam_percent > 100) {
                    $max_possible = $current_rk + 100 * 0.4;
                    $advice = sprintf(
                        "⚠️ Целевой балл недостижим (макс. возможный: %.1f)",
                        $max_possible
                    );
                } else if ($needed_exam_percent < 0) {
                    $advice = "✅ Вы уже достигли целевого балла!";
                } else {
                    $advice = sprintf(
                        "📄 Экзамен: %.1f%%",
                        $needed_exam_percent
                    );
                }
            }
        }
        
        $recommendations[] = [
            'subject' => $subject['name'],
            'current_rk1' => $rk1,
            'current_rk2' => $rk2,
            'current_exam' => $exam_score,
            'current_total' => $current_total,
            'advice' => $advice
        ];
    }
}

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$pageTitle = 'Калькулятор - Student Dark Notebook';
include 'includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">🧮 Калькулятор оценок</h2>
    
    <div class="calculator-info">
        <p>Формула расчета: <strong>(РК1 + РК2) × 0.6 + Экзамен% × 0.4</strong></p>
        <p>Введите желаемый балл, и калькулятор покажет что нужно для его достижения</p>
    </div>
    
    <form method="POST" class="calculator-form">
        <div class="form-group">
            <label for="desired_score">Желаемый итоговый балл:</label>
            <input type="number" id="desired_score" name="desired_score" 
                   min="0" max="160" step="0.1" value="<?php echo $desired_score; ?>" required>
        </div>
        <button type="submit" class="btn-primary">Рассчитать</button>
    </form>

    <?php if (!empty($recommendations)): ?>
    <div class="recommendations">
        <h3 class="section-title">Рекомендации по всем предметам</h3>
        <div class="recommendations-grid">
            <?php foreach ($recommendations as $rec): ?>
                <div class="recommendation-card">
                    <h4><?php echo htmlspecialchars($rec['subject']); ?></h4>
                    <div class="current-scores">
                        <div class="score-item">
                            <span class="score-label">РК1:</span>
                            <span class="score-value"><?php echo number_format($rec['current_rk1'], 1); ?></span>
                        </div>
                        <div class="score-item">
                            <span class="score-label">РК2:</span>
                            <span class="score-value"><?php echo number_format($rec['current_rk2'], 1); ?></span>
                        </div>
                        <div class="score-item">
                            <span class="score-label">Экзамен:</span>
                            <span class="score-value"><?php echo number_format($rec['current_exam'], 1); ?></span>
                        </div>
                        <div class="score-item total">
                            <span class="score-label">Текущий итог:</span>
                            <span class="score-value"><?php echo number_format($rec['current_total'], 1); ?></span>
                        </div>
                    </div>
                    <div class="advice"><?php echo $rec['advice']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
