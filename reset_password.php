<?php
// reset_password.php — Step 2: Set new password
require_once 'includes/db.php';

$token = trim($_GET['token'] ?? '');
$msg   = $err = '';

$stmt = $conn->prepare("SELECT pr.*, u.name FROM password_resets pr JOIN users u ON pr.user_id=u.id WHERE pr.token=? AND pr.expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$reset = $stmt->get_result()->fetch_assoc();

if (!$reset && !isset($_POST['password'])) {
    die("<div style='font-family:sans-serif;padding:2rem;color:red'>Invalid or expired reset link. <a href='/helpdesk/password_reset.php'>Try again</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($password) < 6)       $err = "Password must be at least 6 characters.";
    elseif ($password !== $confirm)   $err = "Passwords do not match.";
    else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $uid  = $reset['user_id'];
        $conn->prepare("UPDATE users SET password=? WHERE id=?")->execute() ?: (function() use($conn,$hash,$uid){
            $u = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $u->bind_param("si", $hash, $uid);
            $u->execute();
        })();
        // Proper update
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $hash, $uid);
        $upd->execute();
        $conn->query("DELETE FROM password_resets WHERE user_id=$uid");
        $msg = "Password reset successfully! <a href='/helpdesk/login.php'>Login now</a>";
    }
}
?>
<!DOCTYPE html><html lang="en">
<head><meta charset="UTF-8"><title>New Password</title>
<link rel="stylesheet" href="/helpdesk/assets/css/style.css"></head>
<body class="auth-page">
<div class="auth-box">
    <h2>🔑 New Password</h2>
    <p class="auth-sub">Hi <?= htmlspecialchars($reset['name'] ?? '') ?>, choose a new password</p>
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if (!$msg): ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" required minlength="6" placeholder="Min 6 characters">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm" required placeholder="Repeat password">
        </div>
        <button type="submit" class="btn-primary w-full">Set New Password</button>
    </form>
    <?php endif; ?>
</div>
</body></html>
