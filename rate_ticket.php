<?php
// rate_ticket.php — CSAT ticket rating (called after ticket resolved)
require_once 'includes/db.php';
require_once 'includes/header.php';
require_login();

$ticket_id = intval($_GET['id'] ?? 0);
$uid       = current_user_id();
$msg = $err = '';

$stmt = $conn->prepare("SELECT t.*, u.name agent FROM tickets t LEFT JOIN users u ON t.assigned_to=u.id WHERE t.id=? AND t.user_id=? AND t.status IN ('Resolved','Closed')");
$stmt->bind_param("ii", $ticket_id, $uid);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) { echo "<div class='alert alert-error' style='margin:2rem'>Ticket not found or not yet resolved.</div>"; require_once 'includes/footer.php'; exit(); }

// Check already rated
$chk = $conn->prepare("SELECT id, rating FROM ticket_ratings WHERE ticket_id=? AND user_id=?");
$chk->bind_param("ii", $ticket_id, $uid);
$chk->execute();
$existing = $chk->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing) {
    $rating  = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5) {
        $ins = $conn->prepare("INSERT INTO ticket_ratings (ticket_id, user_id, rating, comment) VALUES (?,?,?,?)");
        $ins->bind_param("iiis", $ticket_id, $uid, $rating, $comment);
        $ins->execute();
        $msg = "Thank you for your feedback!";
        $existing = ['rating' => $rating];
    } else { $err = "Please select a rating."; }
}
?>
<h1 class="page-title">Rate Your Experience</h1>

<div class="card" style="max-width:560px">
    <div style="background:#f9fafb;border-radius:6px;padding:14px;margin-bottom:1.25rem;border:1px solid #e5e7eb">
        <strong><?= htmlspecialchars($ticket['ticket_no']) ?></strong> — <?= htmlspecialchars($ticket['title']) ?>
        <div style="font-size:12px;color:#6b7280;margin-top:4px">Handled by <?= htmlspecialchars($ticket['agent'] ?? 'N/A') ?></div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

    <?php if ($existing): ?>
        <div style="text-align:center;padding:1.5rem">
            <div style="font-size:36px"><?= str_repeat('⭐', $existing['rating']) ?></div>
            <p style="margin-top:.75rem;color:#6b7280">You rated this ticket <?= $existing['rating'] ?>/5. Thank you!</p>
        </div>
    <?php else: ?>
    <form method="POST">
        <div class="form-group">
            <label>How satisfied were you with the resolution?</label>
            <div id="star-row" style="display:flex;gap:8px;margin-top:.5rem">
                <?php for ($i=1; $i<=5; $i++): ?>
                <label style="cursor:pointer;font-size:28px;transition:transform .1s" title="<?= $i ?> star">
                    <input type="radio" name="rating" value="<?= $i ?>" style="display:none" required>
                    <span class="star" data-val="<?= $i ?>">☆</span>
                </label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Comment (optional)</label>
            <textarea name="comment" rows="3" placeholder="Any feedback for the support team…"></textarea>
        </div>
        <button type="submit" class="btn-primary">Submit Rating</button>
        <a href="my_tickets.php" class="btn-secondary">Skip</a>
    </form>
    <?php endif; ?>
</div>

<script>
const stars = document.querySelectorAll('.star');
stars.forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.val);
        stars.forEach((s,i) => s.textContent = i < val ? '★' : '☆');
        stars.forEach((s,i) => s.style.color = i < val ? '#f59e0b' : '#cbd5e1');
    });
    star.addEventListener('mouseover', function() {
        const val = parseInt(this.dataset.val);
        stars.forEach((s,i) => s.textContent = i < val ? '★' : '☆');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
