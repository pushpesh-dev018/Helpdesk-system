<?php
// includes/auth.php — Session & role helpers
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /helpdesk/login.php");
        exit();
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /helpdesk/index.php");
        exit();
    }
}

function current_user_id()   { return $_SESSION['user_id'] ?? null; }
function current_user_name() { return $_SESSION['name']    ?? 'Guest'; }
function current_role()      { return $_SESSION['role']    ?? 'user'; }
?>
