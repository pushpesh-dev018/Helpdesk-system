<?php
require_once __DIR__ . '/auth.php';

// Get unread notification count
$notif_count = 0;
if (is_logged_in()) {
    $uid = current_user_id();
    $role = current_role();
    $nc = $role === 'admin'
        ? $conn->query("SELECT COUNT(*) c FROM notifications WHERE is_read=0")->fetch_assoc()['c']
        : $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];
    $notif_count = $nc ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk OS</title>
    <link rel="stylesheet" href="/Helpdesk/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">🖥️ HelpDesk OS</div>

    <?php if (is_logged_in()): ?>
    <!-- Desktop Nav -->
    <div class="nav-links">
        <a href="/Helpdesk/index.php">Dashboard</a>
        <?php if (current_role() === 'admin'): ?>
            <a href="/Helpdesk/admin/dashboard.php">Admin</a>
            <a href="/Helpdesk/admin/reports.php">Reports</a>
        <?php endif; ?>
        <a href="/Helpdesk/my_tickets.php">My Tickets</a>
        <a href="/Helpdesk/submit_ticket.php">+ New Ticket</a>
        <a href="/Helpdesk/search.php">🔍</a>

        <!-- Notification Bell -->
        <div class="notif-wrap">
            <button class="notif-bell" onclick="toggleNotif()" title="Notifications">
                🔔
                <span class="notif-count <?= $notif_count == 0 ? 'hidden' : '' ?>" id="notif-badge"><?= $notif_count ?></span>
            </button>
            <div class="notif-dropdown" id="notif-dropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <button class="mark-all-read" onclick="markAllRead()">Mark all read</button>
                </div>
                <div id="notif-list"><div class="notif-empty">Loading...</div></div>
                <div class="notif-footer"><a href="/Helpdesk/notifications.php">View all</a></div>
            </div>
        </div>

        <span class="nav-user">👤 <?= htmlspecialchars(current_user_name()) ?></span>
        <a href="/Helpdesk/logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Hamburger for mobile -->
    <button class="hamburger" onclick="toggleMobileNav()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>

    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobile-nav">
        <a href="/Helpdesk/index.php">📊 Dashboard</a>
        <?php if (current_role() === 'admin'): ?>
            <a href="/Helpdesk/admin/dashboard.php">🛡️ Admin Panel</a>
            <a href="/Helpdesk/admin/reports.php">📈 Reports</a>
            <a href="/Helpdesk/admin/sla_monitor.php">⏱️ SLA Monitor</a>
            <a href="/Helpdesk/admin/audit_log.php">📋 Audit Log</a>
        <?php endif; ?>
        <a href="/Helpdesk/my_tickets.php">🎫 My Tickets</a>
        <a href="/Helpdesk/submit_ticket.php">➕ New Ticket</a>
        <a href="/Helpdesk/search.php">🔍 Search</a>
        <a href="/Helpdesk/notifications.php">🔔 Notifications <?= $notif_count > 0 ? "($notif_count)" : '' ?></a>
        <a href="/Helpdesk/logout.php" style="color:var(--clr-danger)">🚪 Logout</a>
    </div>
    <?php endif; ?>
</nav>
<div class="container">
