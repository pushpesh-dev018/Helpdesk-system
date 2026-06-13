<?php
// search.php — Advanced ticket search & filter
require_once 'includes/db.php';
require_once 'includes/header.php';
require_login();

$uid    = current_user_id();
$role   = current_role();
$q      = trim($_GET['q']        ?? '');
$status = $_GET['status']        ?? '';
$cat    = $_GET['category']      ?? '';
$prio   = $_GET['priority']      ?? '';
$from   = $_GET['date_from']     ?? '';
$to     = $_GET['date_to']       ?? '';
$agent  = intval($_GET['agent']  ?? 0);

// Build WHERE
$conditions = $role === 'admin' ? ['1=1'] : ["t.user_id = $uid"];
$params = []; $types = '';

if ($q) {
    $conditions[] = "(t.title LIKE ? OR t.description LIKE ? OR t.ticket_no LIKE ?)";
    $like = "%$q%"; $params[] = &$like; $params[] = &$like; $params[] = &$like; $types .= 'sss';
}
if ($status)   { $conditions[] = "t.status=?";      $params[] = &$status;  $types .= 's'; }
if ($cat)      { $conditions[] = "t.category=?";    $params[] = &$cat;     $types .= 's'; }
if ($prio)     { $conditions[] = "t.priority=?";    $params[] = &$prio;    $types .= 's'; }
if ($from)     { $conditions[] = "DATE(t.created_at)>=?"; $params[] = &$from; $types .= 's'; }
if ($to)       { $conditions[] = "DATE(t.created_at)<=?"; $params[] = &$to;   $types .= 's'; }
if ($agent)    { $conditions[] = "t.assigned_to=?"; $params[] = &$agent;   $types .= 'i'; }

$where = implode(' AND ', $conditions);
$sql   = "SELECT t.*, u.name requester, a.name agent FROM tickets t
          JOIN users u ON t.user_id=u.id LEFT JOIN users a ON t.assigned_to=a.id
          WHERE $where ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
}
$stmt->execute();
$results = $stmt->get_result();

$agents_list = $conn->query("SELECT id, name FROM users WHERE role='admin'");
?>
<h1 class="page-title">Search Tickets</h1>

<div class="card">
    <form method="GET">
        <div class="form-row" style="grid-template-columns:2fr 1fr 1fr 1fr">
            <div class="form-group">
                <label>Keyword</label>
                <input type="text" name="q" placeholder="Ticket #, title, description…" value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <?php foreach(['Open','In Progress','Resolved','Closed'] as $s): ?>
                        <option <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All</option>
                    <?php foreach(['Hardware','Software','Network','Access','Email','Security','Other'] as $c): ?>
                        <option <?= $cat===$c?'selected':'' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="">All</option>
                    <?php foreach(['Low','Medium','High','Critical'] as $p): ?>
                        <option <?= $prio===$p?'selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row" style="grid-template-columns:1fr 1fr 1fr auto">
            <div class="form-group">
                <label>Date From</label>
                <input type="date" name="date_from" value="<?= $from ?>">
            </div>
            <div class="form-group">
                <label>Date To</label>
                <input type="date" name="date_to" value="<?= $to ?>">
            </div>
            <?php if ($role === 'admin'): ?>
            <div class="form-group">
                <label>Assigned Agent</label>
                <select name="agent">
                    <option value="">All Agents</option>
                    <?php while ($a = $agents_list->fetch_assoc()): ?>
                        <option value="<?= $a['id'] ?>" <?= $agent==$a['id']?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group" style="justify-content:flex-end">
                <label>&nbsp;</label>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn-primary">Search</button>
                    <a href="search.php" class="btn-secondary">Clear</a>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <h3><?= $results->num_rows ?> result(s) found</h3>
        <?php if ($role==='admin'): ?>
        <a href="admin/reports.php?export=csv&<?= http_build_query($_GET) ?>" class="btn-secondary">⬇ Export CSV</a>
        <?php endif; ?>
    </div>
    <table class="table">
        <thead><tr><th>Ticket #</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Requester</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($row = $results->fetch_assoc()): ?>
        <tr>
            <td><strong><?= htmlspecialchars($row['ticket_no']) ?></strong></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['category'] ?></td>
            <td><span class="badge badge-<?= strtolower($row['priority']) ?>"><?= $row['priority'] ?></span></td>
            <td><span class="badge badge-status-<?= strtolower(str_replace(' ','-',$row['status'])) ?>"><?= $row['status'] ?></span></td>
            <td><?= htmlspecialchars($row['requester']) ?></td>
            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
            <td><a href="ticket_detail.php?id=<?= $row['id'] ?>" class="btn-sm">View</a></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
