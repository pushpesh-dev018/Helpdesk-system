<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_login();

$uid  = current_user_id();
$role = current_role();

// Mark all read
if (isset($_GET['mark_all'])) {
    $where = $role === 'admin' ? "1=1" : "user_id=$uid";
    $conn->query("UPDATE notifications SET is_read=1 WHERE $where");
    header("Location: notifications.php"); exit();
}

// Mark one read
if (isset($_GET['read'])) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE id=".intval($_GET['read']));
}

$where  = $role === 'admin' ? "1=1" : "user_id = $uid";
$notifs = $conn->query(
    "SELECT n.*, u.name uname FROM notifications n 
     LEFT JOIN users u ON n.user_id=u.id 
     WHERE $where 
     ORDER BY n.created_at DESC"
);
$unread = $role === 'admin'
    ? $conn->query("SELECT COUNT(*) c FROM notifications WHERE is_read=0")->fetch_assoc()['c']
    : $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];

$icon_map = [
    'ticket_created'  => '🎫',
    'ticket_updated'  => '✏️',
    'ticket_assigned' => '👤',
    'ticket_resolved' => '✅',
    'sla_breach'      => '⚠️',
    'new_note'        => '💬',
    'ticket_rated'    => '⭐',
];
?>

<script>document.getElementById('topbar-title').textContent='Notifications';</script>

<div class="page-header">
    <div>
        <div class="page-title">🔔 Notifications
            <?php if ($unread > 0): ?>
                <span class="badge badge-critical" style="font-size:13px;margin-left:8px"><?= $unread ?> unread</span>
            <?php endif; ?>
        </div>
        <div class="page-sub">All your system notifications</div>
    </div>
    <?php if ($unread > 0): ?>
        <a href="?mark_all=1" class="btn btn-secondary">✓ Mark all read</a>
    <?php endif; ?>
</div>

<div class="card" style="padding:0">
    <?php if ($notifs->num_rows === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">🎉</div>
            <p>No notifications yet!</p>
        </div>
    <?php endif; ?>

    <?php while ($n = $notifs->fetch_assoc()):
        $icon = $icon_map[$n['type']] ?? '🔔';
    ?>
    <div style="display:flex;gap:14px;padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:<?= $n['is_read'] ? '#fff' : '#eff6ff' ?>;cursor:pointer;transition:background .15s"
         onclick="window.location='<?= htmlspecialchars($n['link'] ?? '#') ?>'"
         onmouseover="this.style.background='#f8faff'"
         onmouseout="this.style.background='<?= $n['is_read'] ? '#fff' : '#eff6ff' ?>'">

        <span style="font-size:26px;flex-shrink:0;margin-top:2px"><?= $icon ?></span>

        <div style="flex:1">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem">
                <strong style="font-size:13px;color:var(--text)"><?= htmlspecialchars($n['title']) ?></strong>
                <span style="font-size:11px;color:var(--muted);white-space:nowrap">
                    <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                </span>
            </div>
            <p style="font-size:12px;color:var(--muted);margin-top:3px"><?= htmlspecialchars($n['message']) ?></p>
        </div>

        <?php if (!$n['is_read']): ?>
            <span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:8px"></span>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?>