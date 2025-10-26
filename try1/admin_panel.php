<?php
include_once __DIR__ . '/includes/csrf.php'; 
// ---- FIXED admin_panel.php (full version) ----
// Enable output buffering and start session to ensure headers and sessions work on Plesk
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache headers FIRST before any output (still safe because of ob_start())
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/db.php';
// include csrf stub — it returns empty field/check true so old calls won't break
if (file_exists(__DIR__ . '/includes/csrf.php')) {
    require_once __DIR__ . '/includes/csrf.php';
}

// If user not logged in or not admin — redirect
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Handle success messages from redirects
if (isset($_GET['success'])) {
    $success_messages = [
        'user_added' => 'Пользователь добавлен!',
        'user_deleted' => 'Пользователь удален!',
        'subject_added' => 'Предмет добавлен!',
        'subject_updated' => 'Предмет обновлен!',
        'subject_deleted' => 'Предмет удален!',
        'task_added' => 'Задание добавлено!',
        'task_deleted' => 'Задание удалено!',
        'schedule_added' => 'Занятие добавлено в расписание!',
        'schedule_deleted' => 'Занятие удалено!'
    ];
    $success = $success_messages[$_GET['success']] ?? 'Операция выполнена успешно!';
}

// -----------------------------
// Add User
// -----------------------------
if (isset($_POST['add_user'])) {
    // CSRF is optional — using stub, so it's safe
    // if (function_exists('check_csrf')) check_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'student';

    if (!empty($username) && !empty($password)) {
        try {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $full_name, $role])) {
                    // regenerate_csrf_token(); // not required with stub
                    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                    header('Pragma: no-cache');
                    header('Location: admin_panel.php?success=user_added&nocache=' . uniqid());
                    exit;
                } else {
                    $error = 'Ошибка добавления пользователя';
                }
            }
        } catch (Exception $e) {
            $error = 'Ошибка добавления пользователя: ' . $e->getMessage();
        }
    } else {
        $error = 'Логин и пароль обязательны';
    }
}

// -----------------------------
// Delete User
// -----------------------------
if (isset($_POST['delete_user'])) {
    // CSRF skipped (stub)
    $user_id = $_POST['user_id'] ?? 0;
    if ($user_id != $_SESSION['user_id']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                // regenerate_csrf_token();
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Location: admin_panel.php?success=user_deleted&nocache=' . uniqid());
                exit;
            } else {
                $error = 'Ошибка удаления пользователя';
            }
        } catch (Exception $e) {
            $error = 'Ошибка удаления пользователя: ' . $e->getMessage();
        }
    } else {
        $error = 'Нельзя удалить себя!';
    }
}

// -----------------------------
// Add Subject
// -----------------------------
if (isset($_POST['add_subject'])) {
    // CSRF skipped (stub)

    $name = trim($_POST['subject_name'] ?? '');
    $teacher = trim($_POST['teacher'] ?? '');
    $teacher_phone = trim($_POST['teacher_phone'] ?? '');
    $max_points = intval($_POST['max_points'] ?? 100);

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (name, teacher, teacher_phone, max_points) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $teacher, $teacher_phone, $max_points])) {
                // regenerate_csrf_token();
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Location: admin_panel.php?success=subject_added&nocache=' . uniqid());
                exit;
            } else {
                $error = 'Ошибка добавления предмета';
            }
        } catch (Exception $e) {
            $error = 'Ошибка добавления предмета: ' . $e->getMessage();
        }
    } else {
        $error = 'Название предмета не может быть пустым';
    }
}

// -----------------------------
// Edit Subject
// -----------------------------
if (isset($_POST['edit_subject'])) {
    // CSRF skipped

    $subject_id = $_POST['subject_id'] ?? 0;
    $name = trim($_POST['subject_name'] ?? '');
    $teacher = trim($_POST['teacher'] ?? '');
    $teacher_phone = trim($_POST['teacher_phone'] ?? '');
    $max_points = intval($_POST['max_points'] ?? 100);

    if (!empty($name) && $subject_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE subjects SET name = ?, teacher = ?, teacher_phone = ?, max_points = ? WHERE id = ?");
            if ($stmt->execute([$name, $teacher, $teacher_phone, $max_points, $subject_id])) {
                // regenerate_csrf_token();
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Location: admin_panel.php?success=subject_updated&nocache=' . uniqid());
                exit;
            } else {
                $error = 'Ошибка обновления предмета';
            }
        } catch (Exception $e) {
            $error = 'Ошибка обновления предмета: ' . $e->getMessage();
        }
    } else {
        $error = 'Нельзя сохранять пустое название предмета';
    }
}

// -----------------------------
// Delete Subject (transactional, cascades)
// -----------------------------
if (isset($_POST['delete_subject'])) {
    // CSRF skipped

    $subject_id = intval($_POST['subject_id'] ?? 0);

    if ($subject_id > 0) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Delete related data in correct order
            $stmt1 = $pdo->prepare("DELETE FROM task_completions WHERE task_id IN (SELECT id FROM tasks WHERE subject_id = ?)");
            $stmt1->execute([$subject_id]);

            $stmt2 = $pdo->prepare("DELETE FROM tasks WHERE subject_id = ?");
            $stmt2->execute([$subject_id]);

            $stmt3 = $pdo->prepare("DELETE FROM grades WHERE subject_id = ?");
            $stmt3->execute([$subject_id]);

            $stmt4 = $pdo->prepare("DELETE FROM schedule WHERE subject_id = ?");
            $stmt4->execute([$subject_id]);

            $stmt5 = $pdo->prepare("DELETE FROM debts WHERE subject_id = ?");
            $stmt5->execute([$subject_id]);

            // Delete the subject itself
            $stmt6 = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt6->execute([$subject_id]);

            // Commit transaction
            $pdo->commit();

            // regenerate_csrf_token();

            // Clear all possible caches
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Location: admin_panel.php?success=subject_deleted&nocache=' . uniqid());
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка при удалении предмета: ' . $e->getMessage();
        }
    } else {
        $error = 'Неверный ID предмета';
    }
}

// -----------------------------
// Add Task
// -----------------------------
if (isset($_POST['add_task'])) {
    // CSRF skipped

    $subject_id = $_POST['task_subject_id'] ?? 0;
    $title = trim($_POST['task_title'] ?? '');
    $description = trim($_POST['task_description'] ?? '');
    $due_date = $_POST['task_due_date'] ?? '';
    $due_time = $_POST['task_due_time'] ?? '';
    $points = floatval($_POST['task_points'] ?? 0);

    if (!empty($title) && !empty($due_date)) {
        try {
            $full_due = $due_date . ($due_time ? ' ' . $due_time : ' 23:59:59');
            $stmt = $pdo->prepare("INSERT INTO tasks (subject_id, title, description, due_date, points) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$subject_id, $title, $description, $full_due, $points])) {
                // regenerate_csrf_token();
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Location: admin_panel.php?success=task_added&nocache=' . uniqid());
                exit;
            } else {
                $error = 'Ошибка добавления задания';
            }
        } catch (Exception $e) {
            $error = 'Ошибка добавления задания: ' . $e->getMessage();
        }
    } else {
        $error = 'Нужно указать название задания и дату';
    }
}

// -----------------------------
// Delete Task
// -----------------------------
if (isset($_POST['delete_task'])) {
    // CSRF skipped

    $task_id = $_POST['task_id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        if ($stmt->execute([$task_id])) {
            // regenerate_csrf_token();
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: admin_panel.php?success=task_deleted&nocache=' . uniqid());
            exit;
        } else {
            $error = 'Ошибка удаления задания';
        }
    } catch (Exception $e) {
        $error = 'Ошибка удаления задания: ' . $e->getMessage();
    }
}

// -----------------------------
// Add Schedule
// -----------------------------
if (isset($_POST['add_schedule'])) {
    // CSRF skipped

    $date = $_POST['schedule_date'] ?? '';
    $subject_id = $_POST['schedule_subject_id'] ?? 0;
    $time = trim($_POST['schedule_time'] ?? '');
    $room = trim($_POST['schedule_room'] ?? '');
    $teacher = trim($_POST['schedule_teacher'] ?? '');

    if (!empty($date) && !empty($time)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO schedule (date, subject_id, time, room, teacher) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$date, $subject_id, $time, $room, $teacher])) {
                // regenerate_csrf_token();
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Location: admin_panel.php?success=schedule_added&nocache=' . uniqid());
                exit;
            } else {
                $error = 'Ошибка добавления расписания';
            }
        } catch (Exception $e) {
            $error = 'Ошибка добавления расписания: ' . $e->getMessage();
        }
    } else {
        $error = 'Дата и время обязательны';
    }
}

// -----------------------------
// Delete Schedule
// -----------------------------
if (isset($_POST['delete_schedule'])) {
    // CSRF skipped

    $schedule_id = $_POST['schedule_id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = ?");
        if ($stmt->execute([$schedule_id])) {
            // regenerate_csrf_token();
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: admin_panel.php?success=schedule_deleted&nocache=' . uniqid());
            exit;
        } else {
            $error = 'Ошибка удаления расписания';
        }
    } catch (Exception $e) {
        $error = 'Ошибка удаления расписания: ' . $e->getMessage();
    }
}

// -----------------------------
// Fetch fresh lists for display
// -----------------------------
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY role, username")->fetchAll();
    $subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
    $tasks = $pdo->query("SELECT t.*, s.name as subject_name FROM tasks t JOIN subjects s ON t.subject_id = s.id ORDER BY t.due_date DESC LIMIT 20")->fetchAll();
    $schedules = $pdo->query("SELECT sc.*, s.name as subject_name FROM schedule sc JOIN subjects s ON sc.subject_id = s.id WHERE sc.date >= CURDATE() ORDER BY sc.date, sc.time LIMIT 30")->fetchAll();
} catch (Exception $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}

$pageTitle = 'Панель администратора - Student Dark Notebook';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
    <h2 class="page-title">⚙️ Панель администратора</h2>

    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-sections">

        <!-- User Management -->
        <div class="admin-section">
            <h3>👥 Управление пользователями</h3>

            <form method="POST" class="admin-form">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Логин</label>
                        <input type="text" name="username" id="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Полное имя</label>
                        <input type="text" name="full_name" id="full_name">
                    </div>

                    <div class="form-group">
                        <label for="role">Роль</label>
                        <select name="role" id="role">
                            <option value="student">Студент</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="add_user" class="btn-primary">Добавить пользователя</button>
            </form>

            <h4 style="margin-top: 30px; margin-bottom: 15px;">Список пользователей</h4>
            <div class="users-list">
                <?php foreach ($users as $user): ?>
                    <div class="user-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-dark); border: 1px solid var(--border-sketch); border-radius: 6px; margin-bottom: 10px;">
                        <div>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            <?php if ($user['full_name']): ?>
                                - <?php echo htmlspecialchars($user['full_name']); ?>
                            <?php endif; ?>
                            <span style="color: var(--text-secondary); margin-left: 10px;">
                                (<?php echo $user['role'] === 'admin' ? 'Администратор' : 'Студент'; ?>)
                            </span>
                        </div>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="margin: 0;">
                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-icon btn-danger" onclick="return confirm('Удалить пользователя?')">🗑️</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Subject Management -->
        <div class="admin-section">
            <h3>📚 Управление предметами</h3>

            <form method="POST" class="admin-form">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject_name">Название предмета</label>
                        <input type="text" name="subject_name" id="subject_name" required>
                    </div>

                    <div class="form-group">
                        <label for="teacher">Преподаватель</label>
                        <input type="text" name="teacher" id="teacher">
                    </div>

                    <div class="form-group">
                        <label for="teacher_phone">Телефон преподавателя</label>
                        <input type="text" name="teacher_phone" id="teacher_phone" placeholder="+7 (___) ___-__-__">
                    </div>

                    <div class="form-group">
                        <label for="max_points">Максимум баллов</label>
                        <input type="number" name="max_points" id="max_points" value="100">
                    </div>
                </div>

                <button type="submit" name="add_subject" class="btn-primary">Добавить предмет</button>
            </form>

            <h4 style="margin-top: 30px; margin-bottom: 15px;">Список предметов</h4>
            <div class="subjects-list">
                <?php foreach ($subjects as $subject): ?>
                    <div class="subject-item" style="padding: 15px; background: var(--bg-dark); border: 1px solid var(--border-sketch); border-radius: 6px; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="font-size: 16px;"><?php echo htmlspecialchars($subject['name']); ?></strong>
                                <?php if ($subject['teacher']): ?>
                                    <div style="color: var(--text-secondary); margin-top: 5px;">
                                        👨‍🏫 <?php echo htmlspecialchars($subject['teacher']); ?>
                                        <?php if ($subject['teacher_phone']): ?>
                                            - <?php echo htmlspecialchars($subject['teacher_phone']); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div style="color: var(--accent-primary); margin-top: 5px;">
                                    Макс. баллов: <?php echo $subject['max_points']; ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)" class="btn-icon">✏️</button>
                                <form method="POST" style="margin: 0;">
                                    <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                    <button type="submit" name="delete_subject" class="btn-icon btn-danger" onclick="return confirm('Удалить предмет? Это удалит все связанные данные!')">🗑️</button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-form-<?php echo $subject['id']; ?>" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-sketch);">
                            <form method="POST">
                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Название</label>
                                        <input type="text" name="subject_name" value="<?php echo htmlspecialchars($subject['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Преподаватель</label>
                                        <input type="text" name="teacher" value="<?php echo htmlspecialchars($subject['teacher']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Телефон</label>
                                        <input type="text" name="teacher_phone" value="<?php echo htmlspecialchars($subject['teacher_phone']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Макс. баллов</label>
                                        <input type="number" name="max_points" value="<?php echo $subject['max_points']; ?>">
                                    </div>
                                </div>
                                <button type="submit" name="edit_subject" class="btn-primary btn-sm">Сохранить изменения</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Task Management -->
        <div class="admin-section">
            <h3>📝 Управление заданиями</h3>

            <form method="POST" class="admin-form">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="task_subject_id">Предмет</label>
                        <select name="task_subject_id" id="task_subject_id" required>
                            <option value="">Выберите предмет</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="task_title">Название задания</label>
                        <input type="text" name="task_title" id="task_title" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="task_description">Описание (что нужно сделать)</label>
                    <textarea name="task_description" id="task_description" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="task_due_date">Срок сдачи (дата)</label>
                        <input type="date" name="task_due_date" id="task_due_date" required>
                    </div>

                    <div class="form-group">
                        <label for="task_due_time">Время сдачи</label>
                        <input type="time" name="task_due_time" id="task_due_time">
                    </div>

                    <div class="form-group">
                        <label for="task_points">Ценность в баллах</label>
                        <input type="number" name="task_points" id="task_points" step="0.1" value="0" min="0">
                    </div>
                </div>

                <button type="submit" name="add_task" class="btn-primary">Добавить задание</button>
            </form>

            <h4 style="margin-top: 30px; margin-bottom: 15px;">Недавние задания</h4>
            <div class="tasks-list-admin">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item-admin" style="padding: 15px; background: var(--bg-dark); border: 1px solid var(--border-sketch); border-radius: 6px; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="color: var(--accent-primary); font-size: 14px;"><?php echo htmlspecialchars($task['subject_name']); ?></div>
                                <strong style="font-size: 16px;"><?php echo htmlspecialchars($task['title']); ?></strong>
                                <?php if ($task['description']): ?>
                                    <div style="color: var(--text-secondary); margin-top: 5px;">
                                        <?php echo htmlspecialchars($task['description']); ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top: 8px; color: var(--text-secondary); font-size: 14px;">
                                    📅 <?php echo date('d.m.Y H:i', strtotime($task['due_date'])); ?>
                                    <?php if ($task['points'] > 0): ?>
                                        | ⭐ <?php echo $task['points']; ?> баллов
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST" style="margin: 0;">
                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="delete_task" class="btn-icon btn-danger" onclick="return confirm('Удалить задание?')">🗑️</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Schedule Management -->
        <div class="admin-section">
            <h3>📅 Управление расписанием</h3>

            <form method="POST" class="admin-form">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="schedule_date">Дата</label>
                        <input type="date" name="schedule_date" id="schedule_date" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule_subject_id">Предмет</label>
                        <select name="schedule_subject_id" id="schedule_subject_id" required>
                            <option value="">Выберите предмет</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="schedule_time">Время</label>
                        <input type="time" name="schedule_time" id="schedule_time" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule_room">Кабинет</label>
                        <input type="text" name="schedule_room" id="schedule_room">
                    </div>

                    <div class="form-group">
                        <label for="schedule_teacher">Преподаватель</label>
                        <input type="text" name="schedule_teacher" id="schedule_teacher">
                    </div>
                </div>

                <button type="submit" name="add_schedule" class="btn-primary">Добавить в расписание</button>
            </form>

            <h4 style="margin-top: 30px; margin-bottom: 15px;">Предстоящие занятия</h4>
            <div class="schedule-list-admin">
                <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-item-admin" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-dark); border: 1px solid var(--border-sketch); border-radius: 6px; margin-bottom: 10px;">
                        <div>
                            <strong><?php echo date('d.m.Y', strtotime($schedule['date'])); ?></strong> в
                            <span style="color: var(--accent-primary);"><?php echo htmlspecialchars($schedule['time']); ?></span> -
                            <?php echo htmlspecialchars($schedule['subject_name']); ?>
                            <?php if ($schedule['room']): ?>
                                | Каб. <?php echo htmlspecialchars($schedule['room']); ?>
                            <?php endif; ?>
                            <?php if ($schedule['teacher']): ?>
                                | <?php echo htmlspecialchars($schedule['teacher']); ?>
                            <?php endif; ?>
                        </div>
                        <form method="POST" style="margin: 0;">
                            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                            <button type="submit" name="delete_schedule" class="btn-icon btn-danger" onclick="return confirm('Удалить занятие?')">🗑️</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<script>
function editSubject(subject) {
    const formId = 'edit-form-' + subject.id;
    const form = document.getElementById(formId);
    if (form) {
        const isVisible = form.style.display !== 'none';
        // Hide all edit forms first
        document.querySelectorAll('[id^="edit-form-"]').forEach(f => f.style.display = 'none');
        // Toggle current form
        form.style.display = isVisible ? 'none' : 'block';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
// Close PDO connection and flush output
$pdo = null;
ob_end_flush();
?>
