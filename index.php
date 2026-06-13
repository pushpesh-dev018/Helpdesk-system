<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_login();

$uid  = current_user_id();
$role = current_role();

if ($role === 'admin') {
    $total    = $conn->query("SELECT COUNT(*) c FROM tickets")->fetch_assoc()['c'];
    $open     = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Open'")->fetch_assoc()['c'];
    $progress = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='In Progress'")->fetch_assoc()['c'];
    $resolved = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Resolved'")->fetch_assoc()['c'];
    $breached = $conn->query("SELECT COUNT(*) c FROM tickets WHERE sla_deadline < NOW() AND status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
    $tickets  = $conn->query("SELECT t.*,u.name requester FROM tickets t JOIN users u ON t.user_id=u.id ORDER BY FIELD(t.priority,'Critical','High','Medium','Low'),t.created_at DESC LIMIT 10");
} else {
    $total    = $conn->query("SELECT COUNT(*) c FROM tickets WHERE user_id=$uid")->fetch_assoc()['c'];
    $open     = $conn->query("SELECT COUNT(*) c FROM tickets WHERE user_id=$uid AND status='Open'")->fetch_assoc()['c'];
    $progress = $conn->query("SELECT COUNT(*) c FROM tickets WHERE user_id=$uid AND status='In Progress'")->fetch_assoc()['c'];
    $resolved = $conn->query("SELECT COUNT(*) c FROM tickets WHERE user_id=$uid AND status='Resolved'")->fetch_assoc()['c'];
    $breached = 0;
    $tickets  = $conn->query("SELECT t.*,u.name requester FROM tickets t JOIN users u ON t.user_id=u.id WHERE t.user_id=$uid ORDER BY t.created_at DESC");
}
?>
<script>document.getElementById('topbar-title').textContent='Dashboard';</script>

<div class="page-header">
    <div>
        <div class="page-title">Dashboard 👋</div>
        <div class="page-sub">Welcome back, <?= htmlspecialchars(current_user_name()) ?>! Here's what's happening.</div>
    </div>
    <a href="/Helpdesk/submit_ticket.php" class="btn btn-primary">➕ New Ticket</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🎫</div>
        <div class="stat-num"><?= $total ?></div>
        <div class="stat-label">Total Tickets</div>
    </div>
    <div class="stat-card open">
        <div class="stat-icon">📂</div>
        <div class="stat-num"><?= $open ?></div>
        <div class="stat-label">Open</div>
    </div>
    <div class="stat-card progress">
        <div class="stat-icon">⚙️</div>
        <div class="stat-num"><?= $progress ?></div>
        <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-card resolved">
        <div class="stat-icon">✅</div>
        <div class="stat-num"><?= $resolved ?></div>
        <div class="stat-label">Resolved</div>
    </div>
    <?php if ($role === 'admin'): ?>
    <div class="stat-card breached">
        <div class="stat-icon">⚠️</div>
        <div class="stat-num"><?= $breached ?></div>
        <div class="stat-label">SLA Breached</div>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Tickets -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Recent Tickets</div>
        <a href="<?= $role==='admin'?'/Helpdesk/admin/dashboard.php':'/Helpdesk/my_tickets.php' ?>" class="btn btn-secondary btn-sm">View all →</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket #</th><th>Title</th><th>Category</th>
                    <th>Priority</th><th>Status</th><th>Created</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($tickets->num_rows === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted)">No tickets yet. <a href="/Helpdesk/submit_ticket.php">Create one!</a></td></tr>
            <?php endif; ?>
            <?php while ($row = $tickets->fetch_assoc()): ?>
            <tr>
                <td><strong style="color:var(--primary)"><?= htmlspecialchars($row['ticket_no']) ?></strong></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($row['title']) ?></td>
                <td><span class="chip"><?= $row['category'] ?></span></td>
                <td><span class="badge badge-<?= strtolower($row['priority']) ?>"><?= $row['priority'] ?></span></td>
                <td><span class="badge badge-status-<?= strtolower(str_replace(' ','-',$row['status'])) ?>"><?= $row['status'] ?></span></td>
                <td style="color:var(--muted);font-size:12px"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td><a href="/Helpdesk/ticket_detail.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
