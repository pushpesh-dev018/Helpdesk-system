<?php
// password_reset.php — Step 1: Request reset link
require_once 'includes/db.php';
require_once 'includes/mailer.php';

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt  = $conn->prepare("SELECT id, name FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $uid     = $user['id'];

        // Delete old tokens for this user
        $conn->query("DELETE FROM password_resets WHERE user_id=$uid");

        $ins = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
        $ins->bind_param("iss", $uid, $token, $expires);
        $ins->execute();

        $link = "http://localhost/helpdesk/reset_password.php?token=$token";
        $body = "
        <div style='font-family:sans-serif;max-width:500px;margin:0 auto'>
          <div style='background:#2563eb;padding:20px;border-radius:8px 8px 0 0'><h2 style='color:#fff;margin:0'>🖥️ HelpDesk OS</h2></div>
          <div style='background:#f9fafb;padding:24px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px'>
            <p>Hi <strong>{$user['name']}</strong>, click below to reset your password.</p>
            <p style='color:#6b7280;font-size:13px'>This link expires in 1 hour.</p>
            <a href='$link' style='display:inline-block;background:#2563eb;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;margin-top:8px'>Reset Password</a>
          </div>
        </div>";
        send_mail($email, $user['name'], 'Reset Your HelpDesk Password', $body);
    }
    // Always show success (security — don't reveal if email exists)
    $msg = "If this email is registered, a reset link has been sent.";
}
?>
<!DOCTYPE html><html lang="en">
<head><meta charset="UTF-8"><title>Reset Password</title>
<link rel="stylesheet" href="/helpdesk/assets/css/style.css"></head>
<body class="auth-page">
<div class="auth-box">
    <h2>🔒 Reset Password</h2>
    <p class="auth-sub">Enter your email to receive a reset link</p>
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@company.com">
        </div>
        <button type="submit" class="btn-primary w-full">Send Reset Link</button>
    </form>
    <p style="margin-top:1rem;font-size:13px;text-align:center"><a href="/helpdesk/login.php">← Back to Login</a></p>
</div>
</body></html>
