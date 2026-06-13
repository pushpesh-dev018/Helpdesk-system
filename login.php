<?php
session_start(); require_once 'includes/db.php';
if(isset($_SESSION['user_id'])){header("Location: index.php");exit();}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    $stmt=$conn->prepare("SELECT id,name,password,role FROM users WHERE email=?");
    $stmt->bind_param("s",$email);$stmt->execute();
    $user=$stmt->get_result()->fetch_assoc();$stmt->close();
    if($user&&password_verify($password,$user['password'])){
        $_SESSION['user_id']=$user['id'];$_SESSION['name']=$user['name'];$_SESSION['role']=$user['role'];
        header("Location:".($user['role']==='admin'?'admin/dashboard.php':'index.php'));exit();
    } else { $error="Invalid email or password."; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Login — HelpDesk OS</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="auth-layout">
<div class="auth-left">
    <div class="auth-brand"><div class="auth-brand-icon">🖥️</div><div><div class="auth-brand-name">HelpDesk OS</div><div class="auth-brand-sub">IT Support Platform</div></div></div>
    <div class="auth-tagline"><h1>Manage IT Support <span>Efficiently</span></h1><p>Streamline incident reporting and ticket management from one central platform.</p></div>
    <div class="auth-feats">
        <div class="auth-feat"><div class="auth-feat-icon">🎫</div> SLA-based ticket management</div>
        <div class="auth-feat"><div class="auth-feat-icon">📊</div> Real-time analytics dashboard</div>
        <div class="auth-feat"><div class="auth-feat-icon">🔔</div> Live notifications & email alerts</div>
        <div class="auth-feat"><div class="auth-feat-icon">🔐</div> OTP & bcrypt security</div>
        <div class="auth-feat"><div class="auth-feat-icon">⭐</div> CSAT rating system</div>
    </div>
</div>
<div class="auth-right">
<div class="auth-box">
    <h2>Welcome Back 👋</h2>
    <p class="auth-sub">Sign in to your HelpDesk account</p>
    <?php if($error): ?><div class="alert alert-error">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label class="form-label">Email Address</label>
        <div class="input-wrap"><span class="iicon">✉️</span><input type="email" name="email" required placeholder="you@company.com" autofocus></div></div>
        <div class="form-group"><label class="form-label">Password</label>
        <div class="input-wrap"><span class="iicon">🔒</span><input type="password" name="password" required placeholder="Your password"></div></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;font-size:13px">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer"><input type="checkbox" style="width:auto;padding:0"> Remember me</label>
            <a href="password_reset.php" style="color:var(--primary)">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-full" style="padding:.75rem">Sign In →</button>
    </form>
    <div class="divider">or</div>
    <a href="otp_login.php" class="btn btn-secondary w-full" style="padding:.7rem">🔐 Login with OTP</a>
    <p style="text-align:center;margin-top:1.25rem;font-size:13px;color:var(--muted)">No account? <a href="register.php" style="color:var(--primary);font-weight:600">Register here</a></p>
    <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--border);text-align:center;font-size:11px;color:var(--muted)">🔒 Secured with bcrypt & session auth</div>
</div>
</div>
</div>
<script src="assets/js/main.js"></script></body></html>
