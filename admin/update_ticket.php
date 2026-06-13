<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) { echo "<p>Ticket not found.</p>"; require_once '../includes/footer.php'; exit(); }

$agents  = $conn->query("SELECT id, name FROM users WHERE role='admin'");
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status   = $_POST['status']      ?? $ticket['status'];
    $priority = $_POST['priority']    ?? $ticket['priority'];
    $assigned = intval($_POST['assigned_to'] ?? 0) ?: null;
    $note     = trim($_POST['note']   ?? '');
    $uid      = current_user_id();

    $upd = $conn->prepare("UPDATE tickets SET status=?, priority=?, assigned_to=? WHERE id=?");
    $upd->bind_param("ssii", $status, $priority, $assigned, $id);
    $upd->execute();

    if ($note) {
        $log = $conn->prepare("INSERT INTO ticket_logs (ticket_id, user_id, note) VALUES (?,?,?)");
        $log->bind_param("iis", $id, $uid, $note);
        $log->execute();
    }
    $success = "Ticket updated successfully.";
    // Refresh
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
}
?>
<h1 class="page-title">Edit Ticket — <?= htmlspecialchars($ticket['ticket_no']) ?></h1>

<div class="card" style="max-width:600px">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['Open','In Progress','Resolved','Closed'] as $s): ?>
                        <option <?= $ticket['status']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <?php foreach (['Low','Medium','High','Critical'] as $p): ?>
                        <option <?= $ticket['priority']===$p?'selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Assign To</label>
            <select name="assigned_to">
                <option value="">-- Unassigned --</option>
                <?php $agents->data_seek(0); while ($a = $agents->fetch_assoc()): ?>
                    <option value="<?= $a['id'] ?>" <?= $ticket['assigned_to']==$a['id']?'selected':'' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Add Note (optional)</label>
            <textarea name="note" rows="3" placeholder="Internal update note…"></textarea>
        </div>
        <button type="submit" class="btn-primary">Save Changes</button>
        <a href="dashboard.php" class="btn-secondary">Back</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
