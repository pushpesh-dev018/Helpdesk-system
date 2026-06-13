<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

$total    = $conn->query("SELECT COUNT(*) c FROM tickets")->fetch_assoc()['c'];
$open     = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Open'")->fetch_assoc()['c'];
$progress = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='In Progress'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Resolved'")->fetch_assoc()['c'];
$breached = $conn->query("SELECT COUNT(*) c FROM tickets WHERE sla_deadline < NOW() AND status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];

$tickets  = $conn->query(
    "SELECT t.*, u.name as requester, a.name as agent FROM tickets t
     JOIN users u ON t.user_id = u.id
     LEFT JOIN users a ON t.assigned_to = a.id
     ORDER BY FIELD(t.priority,'Critical','High','Medium','Low'), t.created_at DESC"
);
?>
<h1 class="page-title">Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-num"><?= $total ?></div><div class="stat-label">Total</div></div>
    <div class="stat-card open"><div class="stat-num"><?= $open ?></div><div class="stat-label">Open</div></div>
    <div class="stat-card progress"><div class="stat-num"><?= $progress ?></div><div class="stat-label">In Progress</div></div>
    <div class="stat-card resolved"><div class="stat-num"><?= $resolved ?></div><div class="stat-label">Resolved</div></div>
    <div class="stat-card breached"><div class="stat-num"><?= $breached ?></div><div class="stat-label">SLA Breached</div></div>
</div>

<div class="card">
    <div class="card-header">
        <h2>All Tickets</h2>
        <div>
            <a href="manage_users.php" class="btn-secondary">Manage Users</a>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr><th>Ticket #</th><th>Title</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>SLA</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $tickets->fetch_assoc()):
            $sla_ok = !$row['sla_deadline'] || strtotime($row['sla_deadline']) > time();
        ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['ticket_no']) ?></strong></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['requester']) ?></td>
                <td><?= $row['category'] ?></td>
                <td><span class="badge badge-<?= strtolower($row['priority']) ?>"><?= $row['priority'] ?></span></td>
                <td><span class="badge badge-status-<?= strtolower(str_replace(' ','-',$row['status'])) ?>"><?= $row['status'] ?></span></td>
                <td><?= $row['agent'] ?? '<span style="color:#aaa">Unassigned</span>' ?></td>
                <td><span style="color:<?= $sla_ok?'green':'red' ?>"><?= $sla_ok?'✔ OK':'✘ Breached' ?></span></td>
                <td>
                    <a href="../ticket_detail.php?id=<?= $row['id'] ?>" class="btn-sm">View</a>
                    <a href="update_ticket.php?id=<?= $row['id'] ?>" class="btn-sm">Edit</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
