<?php
// admin/reports.php — Chart.js Dashboard + CSV Export
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

// --- Data for charts ---
$by_status = $conn->query("SELECT status, COUNT(*) c FROM tickets GROUP BY status");
$status_labels = $status_data = [];
while ($r = $by_status->fetch_assoc()) { $status_labels[] = $r['status']; $status_data[] = $r['c']; }

$by_cat = $conn->query("SELECT category, COUNT(*) c FROM tickets GROUP BY category ORDER BY c DESC");
$cat_labels = $cat_data = [];
while ($r = $by_cat->fetch_assoc()) { $cat_labels[] = $r['category']; $cat_data[] = $r['c']; }

$by_priority = $conn->query("SELECT priority, COUNT(*) c FROM tickets GROUP BY priority");
$prio_labels = $prio_data = [];
while ($r = $by_priority->fetch_assoc()) { $prio_labels[] = $r['priority']; $prio_data[] = $r['c']; }

// Last 7 days trend
$daily = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY d");
$day_labels = $day_data = [];
while ($r = $daily->fetch_assoc()) { $day_labels[] = date('d M', strtotime($r['d'])); $day_data[] = $r['c']; }

// Agent performance
$agents = $conn->query("SELECT u.name, COUNT(t.id) total,
    SUM(t.status='Resolved') resolved,
    AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at)) avg_hrs
    FROM users u LEFT JOIN tickets t ON t.assigned_to=u.id
    WHERE u.role='admin' GROUP BY u.id");

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tickets_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Ticket No','Title','Category','Priority','Status','Requester','Assigned To','Created At','SLA Deadline']);
    $all = $conn->query("SELECT t.ticket_no,t.title,t.category,t.priority,t.status,u.name requester,a.name agent,t.created_at,t.sla_deadline FROM tickets t JOIN users u ON t.user_id=u.id LEFT JOIN users a ON t.assigned_to=a.id ORDER BY t.created_at DESC");
    while ($r = $all->fetch_assoc()) fputcsv($out, $r);
    fclose($out); exit();
}
?>
<h1 class="page-title">Reports & Analytics</h1>

<div style="display:flex;gap:10px;margin-bottom:1.5rem">
    <a href="?export=csv" class="btn-primary">⬇ Export CSV</a>
    <a href="?export=pdf" class="btn-secondary">⬇ Export PDF</a>
</div>

<!-- Charts grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">
    <div class="card">
        <h3 style="margin-bottom:1rem">Tickets by Status</h3>
        <canvas id="chartStatus" height="200"></canvas>
    </div>
    <div class="card">
        <h3 style="margin-bottom:1rem">Tickets by Priority</h3>
        <canvas id="chartPriority" height="200"></canvas>
    </div>
    <div class="card">
        <h3 style="margin-bottom:1rem">Tickets by Category</h3>
        <canvas id="chartCategory" height="200"></canvas>
    </div>
    <div class="card">
        <h3 style="margin-bottom:1rem">Last 7 Days Trend</h3>
        <canvas id="chartTrend" height="200"></canvas>
    </div>
</div>

<!-- Agent Performance -->
<div class="card">
    <h3 style="margin-bottom:1rem">Agent Performance</h3>
    <table class="table">
        <thead><tr><th>Agent</th><th>Total Assigned</th><th>Resolved</th><th>Pending</th><th>Avg Resolution Time</th></tr></thead>
        <tbody>
        <?php while ($a = $agents->fetch_assoc()): ?>
        <tr>
            <td><strong><?= htmlspecialchars($a['name']) ?></strong></td>
            <td><?= $a['total'] ?></td>
            <td style="color:green"><?= $a['resolved'] ?></td>
            <td style="color:orange"><?= $a['total'] - $a['resolved'] ?></td>
            <td><?= $a['avg_hrs'] ? round($a['avg_hrs'], 1) . ' hrs' : '—' ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const statusColors   = ['#fbbf24','#38bdf8','#4ade80','#94a3b8'];
const priorityColors = ['#dc2626','#f97316','#2563eb','#16a34a'];
const catColors      = ['#6366f1','#ec4899','#14b8a6','#f59e0b','#8b5cf6','#10b981','#64748b'];

new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($status_labels) ?>, datasets: [{ data: <?= json_encode($status_data) ?>, backgroundColor: statusColors }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('chartPriority'), {
    type: 'bar',
    data: { labels: <?= json_encode($prio_labels) ?>, datasets: [{ label: 'Tickets', data: <?= json_encode($prio_data) ?>, backgroundColor: priorityColors }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('chartCategory'), {
    type: 'bar',
    data: { labels: <?= json_encode($cat_labels) ?>, datasets: [{ label: 'Tickets', data: <?= json_encode($cat_data) ?>, backgroundColor: catColors }] },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('chartTrend'), {
    type: 'line',
    data: { labels: <?= json_encode($day_labels) ?>, datasets: [{ label: 'Tickets', data: <?= json_encode($day_data) ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.1)', fill: true, tension: .3 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>

<?php require_once '../includes/footer.php'; ?>
