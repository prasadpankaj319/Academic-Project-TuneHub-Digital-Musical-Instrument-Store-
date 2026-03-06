<?php
/**
 * includes/auth.php
 * Authentication middleware.
 */
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . base_url('modules/user/login.php'));
        exit;
    }
}

/**
 * Check if user is Admin
 */
function require_admin() {
    require_login();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
        header("HTTP/1.0 403 Forbidden");
        echo "<div style='font-family:sans-serif; text-align:center; padding: 50px;'>";
        echo "<h2>403 Forbidden</h2><p>You do not have permission to access the admin panel.</p>";
        echo "<a href='" . base_url('index.php') . "'>Return Home</a>";
        echo "</div>";
        exit;
    }
}
?>
