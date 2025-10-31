
<?php
/**
 * CSRF Protection Functions
 */

/**
 * Generate CSRF token if it doesn't exist
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get current CSRF token
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * Generate CSRF field for forms
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Check CSRF token
 */
function check_csrf() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        echo "<pre>⚠️ CSRF токен отсутствует</pre>";
        return;
    }
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<pre>❌ CSRF не совпадает: POST=".$_POST['csrf_token']." SESSION=".$_SESSION['csrf_token']."</pre>";
        return;
    }
    echo "<pre>✅ CSRF проверен</pre>";
}


/**
 * Regenerate CSRF token (call after successful form submission)
 */
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
