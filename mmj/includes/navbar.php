<div class="mobile-menu-overlay" id="menuOverlay"></div>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="logo">📓 Дневник</span>
        <button class="close-menu" id="closeMenu">&times;</button>
    </div>
    <ul class="nav-menu">
        <li><a href="/index.php">🏠 Главная</a></li>
        <li><a href="/grades.php">📓 Оценки</a></li>
        <li><a href="/calculator.php">🧮 Калькулятор</a></li>
        <li><a href="/tasks.php">📚 Задания</a></li>
        <li><a href="/debts.php">🧾 Долги</a></li>
        <li><a href="/rating.php">📖 Журнал оценок</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li><a href="/admin_panel.php">⚙️ Панель администратора</a></li>
        <?php endif; ?>
        <li class="nav-divider"></li>
        <li><a href="/logout.php">🚪 Выход</a></li>
    </ul>
</nav>

<div class="top-bar">
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="user-info">
        <span class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Студент'); ?></span>
    </div>
</div>
