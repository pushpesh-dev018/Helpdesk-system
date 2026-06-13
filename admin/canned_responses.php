<?php
// admin/canned_responses.php — Manage preset replies
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $title = trim($_POST['title']); $body = trim($_POST['body']); $uid = current_user_id();
        if ($title && $body) {
            $ins = $conn->prepare("INSERT INTO canned_responses (title, body, created_by) VALUES (?,?,?)");
            $ins->bind_param("ssi", $title, $body, $uid);
            $ins->execute() ? $msg = "Response added." : $err = "Failed.";
        }
    }
}
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM canned_responses WHERE id=".intval($_GET['delete']));
    $msg = "Deleted.";
}

$responses = $conn->query("SELECT cr.*, u.name FROM canned_responses cr JOIN users u ON cr.created_by=u.id ORDER BY cr.title");
?>
<h1 class="page-title">Canned Responses</h1>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">
    <div class="card">
        <h3 style="margin-bottom:1rem">Saved Responses</h3>
        <?php if ($responses->num_rows === 0): ?>
            <p style="color:#6b7280;font-size:13px">No canned responses yet.</p>
        <?php endif; ?>
        <?php while ($r = $responses->fetch_assoc()): ?>
        <div style="padding:12px 0;border-bottom:1px solid #e5e7eb">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <strong style="font-size:14px"><?= htmlspecialchars($r['title']) ?></strong>
                <a href="?delete=<?= $r['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:.4rem 0"><?= nl2br(htmlspecialchars($r['body'])) ?></p>
            <span style="font-size:11px;color:#94a3b8">By <?= htmlspecialchars($r['name']) ?></span>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem">Add New Response</h3>
        <form method="POST">
            <div class="form-group"><label>Title / Shortcut</label><input type="text" name="title" required placeholder="e.g. Hardware — Restart steps"></div>
            <div class="form-group"><label>Response Body</label><textarea name="body" rows="6" required placeholder="Type the full response…"></textarea></div>
            <button type="submit" name="add" class="btn-primary">Save Response</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
