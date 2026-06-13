<?php
require_once __DIR__ . '/auth.php';

// Notification count
$notif_count = 0;
if (is_logged_in()) {
    $uid  = current_user_id();
    $role = current_role();
    $nc   = $role === 'admin'
        ? $conn->query("SELECT COUNT(*) c FROM notifications WHERE is_read=0")->fetch_assoc()['c']
        : $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];
    $notif_count = $nc ?? 0;
}

// Get initials for avatar
function getInitials($name) {
    $words = explode(' ', trim($name));
    $init  = '';
    foreach (array_slice($words, 0, 2) as $w) $init .= strtoupper($w[0]);
    return $init;
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_admin     = is_logged_in() && current_role() === 'admin';
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

<?php if (is_logged_in()): ?>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🖥️</div>
        <div>
            <div class="logo-text">HelpDesk OS</div>
            <div class="logo-sub">IT Support</div>
        </div>
    </div>

    <div class="nav-section">
        <div class="nav-label">Main</div>
        <a href="/Helpdesk/index.php" class="nav-item <?= $current_page==='index.php'?'active':'' ?>">
            <span class="nicon">📊</span> Dashboard
        </a>
        <a href="/Helpdesk/submit_ticket.php" class="nav-item <?= $current_page==='submit_ticket.php'?'active':'' ?>">
            <span class="nicon">➕</span> New Ticket
        </a>
        <a href="/Helpdesk/my_tickets.php" class="nav-item <?= $current_page==='my_tickets.php'?'active':'' ?>">
            <span class="nicon">🎫</span> My Tickets
        </a>
        <a href="/Helpdesk/search.php" class="nav-item <?= $current_page==='search.php'?'active':'' ?>">
            <span class="nicon">🔍</span> Search
        </a>
        <a href="/Helpdesk/notifications.php" class="nav-item <?= $current_page==='notifications.php'?'active':'' ?>">
            <span class="nicon">🔔</span> Notifications
            <?php if ($notif_count > 0): ?><span class="nav-badge"><?= $notif_count ?></span><?php endif; ?>
        </a>

        <?php if ($is_admin): ?>
        <div class="nav-label" style="margin-top:.75rem">Admin</div>
        <a href="/Helpdesk/admin/dashboard.php" class="nav-item <?= $current_page==='dashboard.php'?'active':'' ?>">
            <span class="nicon">🛡️</span> Admin Panel
        </a>
        <a href="/Helpdesk/admin/reports.php" class="nav-item <?= $current_page==='reports.php'?'active':'' ?>">
            <span class="nicon">📈</span> Reports
        </a>
        <a href="/Helpdesk/admin/sla_monitor.php" class="nav-item <?= $current_page==='sla_monitor.php'?'active':'' ?>">
            <span class="nicon">⏱️</span> SLA Monitor
        </a>
        <a href="/Helpdesk/admin/manage_users.php" class="nav-item <?= $current_page==='manage_users.php'?'active':'' ?>">
            <span class="nicon">👥</span> Users
        </a>
        <a href="/Helpdesk/admin/audit_log.php" class="nav-item <?= $current_page==='audit_log.php'?'active':'' ?>">
            <span class="nicon">📋</span> Audit Log
        </a>
        <a href="/Helpdesk/admin/canned_responses.php" class="nav-item <?= $current_page==='canned_responses.php'?'active':'' ?>">
            <span class="nicon">💬</span> Canned Replies
        </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-av"><?= getInitials(current_user_name()) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars(current_user_name()) ?></div>
                <div class="user-role"><?= ucfirst(current_role()) ?></div>
                <div class="status-dot">Online</div>
            </div>
        </div>
        <a href="/Helpdesk/logout.php" style="display:flex;align-items:center;gap:8px;margin-top:.85rem;font-size:12px;color:#64748b;text-decoration:none;padding:.4rem .5rem;border-radius:6px;transition:all .15s" onmouseover="this.style.background='rgba(220,38,38,.1)';this.style.color='#dc2626'" onmouseout="this.style.background='';this.style.color='#64748b'">
            🚪 Logout
        </a>
    </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <button class="hamburger" onclick="toggleSidebar()">☰</button>
    <div class="topbar-title" id="topbar-title">HelpDesk OS</div>

    <div class="topbar-search">
        <span>🔍</span>
        <input type="text" placeholder="Search tickets..." onkeypress="if(event.key==='Enter') window.location='/Helpdesk/search.php?q='+this.value">
    </div>

    <!-- Bell -->
    <div class="notif-wrap">
        <button class="notif-bell" onclick="toggleNotif()">
            🔔
            <span class="notif-count <?= $notif_count==0?'hidden':'' ?>" id="notif-badge"><?= $notif_count ?></span>
        </button>
        <div class="notif-dropdown" id="notif-dropdown">
            <div class="notif-header">
                <span>Notifications</span>
                <button class="mark-all-read" onclick="markAllRead()">Mark all read</button>
            </div>
            <div id="notif-list"><div class="notif-empty">Loading...</div></div>
            <div class="notif-footer"><a href="/Helpdesk/notifications.php">View all notifications</a></div>
        </div>
    </div>

    <div class="avatar" title="<?= htmlspecialchars(current_user_name()) ?>"><?= getInitials(current_user_name()) ?></div>
</header>

<!-- MAIN -->
<main class="main-content">

<?php else: ?>
<main>
<?php endif; ?>
