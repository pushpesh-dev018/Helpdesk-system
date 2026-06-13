<?php
// ticket_detail.php — UPDATED with attachments, canned responses, rating link, audit log
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/audit.php';
require_once 'includes/file_upload.php';
require_login();

$id  = intval($_GET['id'] ?? 0);
$uid = current_user_id();

$stmt = $conn->prepare("SELECT t.*,u.name requester,u.email req_email,a.name agent_name FROM tickets t JOIN users u ON t.user_id=u.id LEFT JOIN users a ON t.assigned_to=a.id WHERE t.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket || (current_role()!=='admin' && $ticket['user_id']!=$uid)) {
    echo "<div class='alert alert-error'>Ticket not found or access denied.</div>";
    require_once 'includes/footer.php'; exit();
}

// Add note + optional file
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['note'])) {
    $note = trim($_POST['note']);
    if ($note) {
        $ins = $conn->prepare("INSERT INTO ticket_logs (ticket_id,user_id,note) VALUES (?,?,?)");
        $ins->bind_param("iis", $id, $uid, $note);
        $ins->execute();
        audit_log($conn, $uid, 'note_added', 'ticket', $id, "Note added to $ticket[ticket_no]");
    }
    // File with note
    if (!empty($_FILES['attach']['name'])) {
        $up = handle_upload($_FILES['attach']);
        if ($up['ok']) {
            save_attachment_db($conn, $id, $uid, $up['filename'], $up['original']);
            audit_log($conn, $uid, 'attachment_added', 'ticket', $id, $up['original']." attached");
        }
    }
    header("Location: ticket_detail.php?id=$id"); exit();
}

$logs        = $conn->query("SELECT l.*,u.name FROM ticket_logs l JOIN users u ON l.user_id=u.id WHERE l.ticket_id=$id ORDER BY l.created_at ASC");
$attachments = get_ticket_attachments($conn, $id);
$canned      = current_role()==='admin' ? $conn->query("SELECT * FROM canned_responses ORDER BY title") : null;

// Existing rating
$rating_row = null;
if ($ticket['status']==='Resolved' || $ticket['status']==='Closed') {
    $r = $conn->prepare("SELECT rating FROM ticket_ratings WHERE ticket_id=? AND user_id=?");
    $r->bind_param("ii", $id, $uid);
    $r->execute();
    $rating_row = $r->get_result()->fetch_assoc();
}
?>
<h1 class="page-title"><?= htmlspecialchars($ticket['ticket_no']) ?> — <?= htmlspecialchars($ticket['title']) ?></h1>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
<div>
    <div class="card">
        <h3 style="margin-bottom:.75rem">Description</h3>
        <p style="line-height:1.7;color:#555"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
    </div>

    <?php if ($attachments): ?>
    <div class="card" style="margin-top:1rem">
        <h3 style="margin-bottom:.75rem">📎 Attachments</h3>
        <?php foreach ($attachments as $att): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #e5e7eb">
            <span style="font-size:20px"><?= preg_match('/\.(jpg|png|gif|jpeg)$/i',$att['original_name']) ? '🖼️' : '📄' ?></span>
            <div>
                <a href="uploads/<?= htmlspecialchars($att['filename']) ?>" target="_blank" style="font-weight:500;color:#2563eb"><?= htmlspecialchars($att['original_name']) ?></a>
                <div style="font-size:11px;color:#94a3b8"><?= date('d M Y', strtotime($att['created_at'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-top:1rem">
        <h3 style="margin-bottom:1rem">Activity Log</h3>
        <?php while ($log = $logs->fetch_assoc()): ?>
            <div class="log-entry">
                <strong><?= htmlspecialchars($log['name']) ?></strong>
                <span class="log-time"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></span>
                <p><?= nl2br(htmlspecialchars($log['note'])) ?></p>
            </div>
        <?php endwhile; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top:1rem">
            <?php if ($canned && $canned->num_rows > 0): ?>
            <div class="form-group">
                <label>Canned Response</label>
                <select id="canned-select" onchange="insertCanned(this)">
                    <option value="">— Select preset reply —</option>
                    <?php while ($c = $canned->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['body']) ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Add Note / Reply</label>
                <textarea name="note" id="note-box" rows="3" placeholder="Type your update…"></textarea>
            </div>
            <div class="form-group">
                <label>Attach File (optional)</label>
                <input type="file" name="attach" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx" style="padding:.3rem">
            </div>
            <button type="submit" class="btn-primary">Add Note</button>
        </form>
    </div>
</div>

<div>
    <div class="card">
        <h3 style="margin-bottom:1rem">Ticket Info</h3>
        <table class="info-table">
            <tr><td>Status</td>   <td><span class="badge badge-status-<?= strtolower(str_replace(' ','-',$ticket['status'])) ?>"><?= $ticket['status'] ?></span></td></tr>
            <tr><td>Priority</td> <td><span class="badge badge-<?= strtolower($ticket['priority']) ?>"><?= $ticket['priority'] ?></span></td></tr>
            <tr><td>Category</td> <td><?= $ticket['category'] ?></td></tr>
            <tr><td>Requester</td><td><?= htmlspecialchars($ticket['requester']) ?></td></tr>
            <tr><td>Assigned</td> <td><?= htmlspecialchars($ticket['agent_name'] ?? 'Unassigned') ?></td></tr>
            <tr><td>SLA</td>      <td><?= $ticket['sla_deadline'] ? date('d M, H:i', strtotime($ticket['sla_deadline'])) : '—' ?></td></tr>
            <tr><td>Created</td>  <td><?= date('d M Y', strtotime($ticket['created_at'])) ?></td></tr>
        </table>

        <?php if (current_role()==='admin'): ?>
        <div style="margin-top:1rem;display:flex;gap:8px;flex-wrap:wrap">
            <a href="admin/update_ticket.php?id=<?= $id ?>" class="btn-primary">✏️ Edit</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (in_array($ticket['status'],['Resolved','Closed']) && $ticket['user_id']==$uid): ?>
    <div class="card" style="margin-top:1rem;text-align:center">
        <h3 style="margin-bottom:.5rem">Rate Your Experience</h3>
        <?php if ($rating_row): ?>
            <div style="font-size:28px"><?= str_repeat('⭐',$rating_row['rating']) ?></div>
            <p style="font-size:13px;color:#6b7280;margin-top:.5rem">You rated <?= $rating_row['rating'] ?>/5</p>
        <?php else: ?>
            <p style="font-size:13px;color:#6b7280;margin-bottom:.75rem">How was your support experience?</p>
            <a href="rate_ticket.php?id=<?= $id ?>" class="btn-primary">⭐ Rate Now</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
</div>

<script>
function insertCanned(sel) {
    if (sel.value) {
        document.getElementById('note-box').value = sel.value;
        sel.selectedIndex = 0;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
