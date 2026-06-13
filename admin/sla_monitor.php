<?php
// admin/sla_monitor.php — SLA breach monitor + alert sender
require_once '../includes/db.php';
require_once '../includes/mailer.php';
require_once '../includes/header.php';
require_admin();

// Send alerts for tickets breaching in next 1 hour
if (isset($_GET['send_alerts'])) {
    $at_risk = $conn->query(
        "SELECT t.*, u.email, u.name FROM tickets t JOIN users u ON t.user_id=u.id
         WHERE t.sla_deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)
         AND t.status NOT IN ('Resolved','Closed')"
    );
    $sent = 0;
    $admin_email = $conn->query("SELECT email FROM users WHERE role='admin' LIMIT 1")->fetch_assoc()['email'] ?? '';
    while ($t = $at_risk->fetch_assoc()) {
        mail_sla_breach_alert($admin_email, $t['ticket_no'], $t['title'], $t['priority']);
        $sent++;
    }
    $msg = "Sent $sent SLA breach alert(s).";
}

// Stats
$breached = $conn->query("SELECT COUNT(*) c FROM tickets WHERE sla_deadline < NOW() AND status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
$at_risk  = $conn->query("SELECT COUNT(*) c FROM tickets WHERE sla_deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 HOUR) AND status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
$healthy  = $conn->query("SELECT COUNT(*) c FROM tickets WHERE sla_deadline > DATE_ADD(NOW(), INTERVAL 2 HOUR) AND status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];

// All open tickets with SLA info
$tickets = $conn->query(
    "SELECT t.*, u.name requester, a.name agent,
     TIMESTAMPDIFF(MINUTE, NOW(), t.sla_deadline) mins_left
     FROM tickets t
     JOIN users u ON t.user_id=u.id
     LEFT JOIN users a ON t.assigned_to=a.id
     WHERE t.status NOT IN ('Resolved','Closed')
     ORDER BY t.sla_deadline ASC"
);
?>
<h1 class="page-title">SLA Monitor</h1>

<?php if (isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="stats-grid" style="margin-bottom:1.5rem">
    <div class="stat-card breached"><div class="stat-num" style="color:#dc2626"><?= $breached ?></div><div class="stat-label">Breached</div></div>
    <div class="stat-card" style="border-left:3px solid #f97316"><div class="stat-num" style="color:#f97316"><?= $at_risk ?></div><div class="stat-label">At Risk (&lt;2hr)</div></div>
    <div class="stat-card resolved"><div class="stat-num" style="color:#16a34a"><?= $healthy ?></div><div class="stat-label">Healthy</div></div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <h3>Open Tickets — SLA Status</h3>
        <a href="?send_alerts=1" class="btn-primary" onclick="return confirm('Send breach alerts to admin?')">⚡ Send Breach Alerts</a>
    </div>
    <table class="table">
        <thead>
            <tr><th>Ticket #</th><th>Title</th><th>Priority</th><th>Assigned</th><th>SLA Deadline</th><th>Time Left</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $tickets->fetch_assoc()):
            $mins = $row['mins_left'];
            if ($mins < 0)    { $label = '⛔ Breached';    $color = '#dc2626'; }
            elseif ($mins < 60)  { $label = '🔴 < 1 hr';  $color = '#dc2626'; }
            elseif ($mins < 120) { $label = '🟠 < 2 hrs'; $color = '#f97316'; }
            elseif ($mins < 480) { $label = '🟡 '.round($mins/60).' hrs'; $color = '#d97706'; }
            else                 { $label = '🟢 '.round($mins/60).' hrs'; $color = '#16a34a'; }
        ?>
        <tr>
            <td><strong><?= htmlspecialchars($row['ticket_no']) ?></strong></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><span class="badge badge-<?= strtolower($row['priority']) ?>"><?= $row['priority'] ?></span></td>
            <td><?= htmlspecialchars($row['agent'] ?? 'Unassigned') ?></td>
            <td style="font-size:12px"><?= $row['sla_deadline'] ? date('d M, H:i', strtotime($row['sla_deadline'])) : '—' ?></td>
            <td><strong style="color:<?= $color ?>"><?= $label ?></strong></td>
            <td><a href="../ticket_detail.php?id=<?= $row['id'] ?>" class="btn-sm">View</a>
                <a href="update_ticket.php?id=<?= $row['id'] ?>" class="btn-sm">Edit</a></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
