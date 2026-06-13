<?php
// admin/audit_log.php — Full audit trail viewer
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

$page     = max(1, intval($_GET['page'] ?? 1));
$per_page = 30;
$offset   = ($page - 1) * $per_page;

$total_rows = $conn->query("SELECT COUNT(*) c FROM audit_logs")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $per_page);

$logs = $conn->query(
    "SELECT al.*, u.name FROM audit_logs al
     JOIN users u ON al.user_id = u.id
     ORDER BY al.created_at DESC
     LIMIT $per_page OFFSET $offset"
);

$action_icons = [
    'ticket_created'  => '🎫',
    'ticket_updated'  => '✏️',
    'ticket_deleted'  => '🗑️',
    'user_login'      => '🔐',
    'user_logout'     => '🚪',
    'user_created'    => '👤',
    'user_deleted'    => '❌',
    'attachment_added'=> '📎',
    'ticket_rated'    => '⭐',
];
?>
<h1 class="page-title">Audit Log</h1>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <span style="color:#6b7280;font-size:13px"><?= number_format($total_rows) ?> total entries</span>
        <a href="?export=csv" class="btn-secondary">⬇ Export CSV</a>
    </div>

    <?php if (isset($_GET['export'])): 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_log_'.date('Y-m-d').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['Time','User','Action','Target','Details','IP']);
        $all = $conn->query("SELECT al.*,u.name FROM audit_logs al JOIN users u ON al.user_id=u.id ORDER BY al.created_at DESC");
        while ($r = $all->fetch_assoc()) fputcsv($out, [$r['created_at'],$r['name'],$r['action'],$r['target_type'].':'.$r['target_id'],$r['details'],$r['ip_address']]);
        fclose($out); exit();
    endif; ?>

    <table class="table">
        <thead>
            <tr><th>Time</th><th>User</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr>
        </thead>
        <tbody>
        <?php while ($log = $logs->fetch_assoc()):
            $icon = $action_icons[$log['action']] ?? '📋';
        ?>
            <tr>
                <td style="font-size:12px;color:#6b7280;white-space:nowrap"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></td>
                <td><strong><?= htmlspecialchars($log['name']) ?></strong></td>
                <td><?= $icon ?> <span style="font-size:12px"><?= htmlspecialchars($log['action']) ?></span></td>
                <td style="font-size:12px;color:#6b7280"><?= $log['target_type'] ?><?= $log['target_id'] ? ':'.$log['target_id'] : '' ?></td>
                <td style="font-size:12px"><?= htmlspecialchars($log['details']) ?></td>
                <td style="font-size:11px;color:#94a3b8"><?= htmlspecialchars($log['ip_address']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div style="display:flex;gap:6px;margin-top:1rem;justify-content:center">
        <?php for ($p=1; $p<=$total_pages; $p++): ?>
            <a href="?page=<?= $p ?>" style="padding:4px 10px;border:1px solid #e5e7eb;border-radius:4px;font-size:13px;text-decoration:none;<?= $p==$page ? 'background:#2563eb;color:#fff;border-color:#2563eb' : 'color:#374151' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
