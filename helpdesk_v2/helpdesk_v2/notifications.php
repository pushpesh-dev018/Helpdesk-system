<?php
// notifications.php — Full notifications page
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

$where   = $role === 'admin' ? "1=1" : "n.user_id = $uid";
$notifs  = $conn->query("SELECT n.*, u.name uname FROM notifications n LEFT JOIN users u ON n.user_id=u.id WHERE $where ORDER BY n.created_at DESC");
$unread  = $role === 'admin'
    ? $conn->query("SELECT COUNT(*) c FROM notifications WHERE is_read=0")->fetch_assoc()['c']
    : $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];

$icon_map = ['ticket_created'=>'🎫','ticket_updated'=>'✏️','ticket_assigned'=>'👤','ticket_resolved'=>'✅','sla_breach'=>'⚠️','new_note'=>'💬','ticket_rated'=>'⭐'];
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem">
    <h1 class="page-title" style="margin:0">🔔 Notifications <?= $unread > 0 ? "<span class='badge badge-critical'>$unread unread</span>" : '' ?></h1>
    <?php if ($unread > 0): ?>
        <a href="?mark_all=1" class="btn-secondary">✓ Mark all read</a>
    <?php endif; ?>
</div>

<div class="card" style="padding:0">
    <?php if ($notifs->num_rows === 0): ?>
        <div style="padding:3rem;text-align:center;color:var(--clr-muted)">
            <div style="font-size:48px;margin-bottom:1rem">🎉</div>
            <p>No notifications yet!</p>
        </div>
    <?php endif; ?>

    <?php while ($n = $notifs->fetch_assoc()):
        $icon = $icon_map[$n['type']] ?? '🔔';
    ?>
    <div style="display:flex;gap:12px;padding:1rem 1.25rem;border-bottom:1px solid var(--clr-border);background:<?= $n['is_read'] ? '#fff' : '#eff6ff' ?>;cursor:pointer"
         onclick="window.location='<?= htmlspecialchars($n['link'] ?? '#') ?>&read_notif=<?= $n['id'] ?>'">
        <span style="font-size:24px;flex-shrink:0"><?= $icon ?></span>
        <div style="flex:1">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;flex-wrap:wrap">
                <strong style="font-size:13px;color:var(--clr-text)"><?= htmlspecialchars($n['title']) ?></strong>
                <span style="font-size:11px;color:var(--clr-muted);white-space:nowrap"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
            </div>
            <p style="font-size:12px;color:var(--clr-muted);margin-top:3px"><?= htmlspecialchars($n['message']) ?></p>
        </div>
        <?php if (!$n['is_read']): ?>
            <span style="width:8px;height:8px;border-radius:50%;background:var(--clr-primary);flex-shrink:0;margin-top:6px"></span>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
