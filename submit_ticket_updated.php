<?php
// submit_ticket.php — UPDATED with file upload + email notification + audit log
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/mailer.php';
require_once 'includes/audit.php';
require_once 'includes/file_upload.php';
require_login();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? '';
    $priority    = $_POST['priority'] ?? 'Medium';
    $uid         = current_user_id();

    $sla_map  = ['Critical'=>4,'High'=>8,'Medium'=>24,'Low'=>72];
    $sla_hrs  = $sla_map[$priority] ?? 24;
    $sla_time = date('Y-m-d H:i:s', strtotime("+{$sla_hrs} hours"));
    $last      = $conn->query("SELECT MAX(id) AS mid FROM tickets")->fetch_assoc()['mid'] ?? 0;
    $ticket_no = 'TKT-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO tickets (ticket_no,user_id,title,description,category,priority,sla_deadline) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sisssss", $ticket_no, $uid, $title, $description, $category, $priority, $sla_time);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;

        // Log note
        $note = "Ticket created by " . current_user_name();
        $log  = $conn->prepare("INSERT INTO ticket_logs (ticket_id,user_id,note) VALUES (?,?,?)");
        $log->bind_param("iis", $new_id, $uid, $note);
        $log->execute();

        // File upload (optional)
        if (!empty($_FILES['attachment']['name'])) {
            $upload = handle_upload($_FILES['attachment']);
            if ($upload['ok']) {
                save_attachment_db($conn, $new_id, $uid, $upload['filename'], $upload['original']);
                audit_log($conn, $uid, 'attachment_added', 'ticket', $new_id, $upload['original'] . " uploaded");
            }
        }

        // Email notification
        $user_row = $conn->query("SELECT email, name FROM users WHERE id=$uid")->fetch_assoc();
        mail_ticket_created($user_row['email'], $user_row['name'], $ticket_no, $title, $priority);

        // Audit log
        audit_log($conn, $uid, 'ticket_created', 'ticket', $new_id, "$ticket_no created");

        $success = "Ticket <strong>$ticket_no</strong> submitted! <a href='ticket_detail.php?id=$new_id'>View it</a>";
    } else {
        $error = "Submit failed. Try again.";
    }
}
?>
<h1 class="page-title">Submit New Ticket</h1>
<div class="card" style="max-width:700px">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <?php foreach(['Hardware','Software','Network','Access','Email','Security','Other'] as $c): ?>
                        <option><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority *</label>
                <select name="priority" required>
                    <option>Low</option><option selected>Medium</option><option>High</option><option>Critical</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Subject *</label>
            <input type="text" name="title" required placeholder="Brief issue summary" maxlength="255">
        </div>
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" rows="5" required placeholder="Describe in detail — what happened, error message, steps…"></textarea>
        </div>
        <div class="form-group">
            <label>Attach File (optional — max 5MB)</label>
            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx" style="padding:.4rem">
            <small style="color:#6b7280;font-size:12px">Allowed: images, PDF, Word, text files</small>
        </div>
        <button type="submit" class="btn-primary">Submit Ticket</button>
        <a href="index.php" class="btn-secondary">Cancel</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>
