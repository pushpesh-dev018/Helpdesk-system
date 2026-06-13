<?php
session_start(); require_once 'includes/db.php';
$error=$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');
    $password=$_POST['password']??'';$confirm=$_POST['confirm']??'';$dept=trim($_POST['department']??'');
    if(strlen($password)<6){$error="Password min 6 characters.";}
    elseif($password!==$confirm){$error="Passwords do not match.";}
    else{
        $chk=$conn->prepare("SELECT id FROM users WHERE email=?");$chk->bind_param("s",$email);$chk->execute();$chk->store_result();
        if($chk->num_rows>0){$error="Email already registered.";}
        else{
            $hash=password_hash($password,PASSWORD_BCRYPT);
            $stmt=$conn->prepare("INSERT INTO users(name,email,password,role,department) VALUES(?,?,?,'user',?)");
            $stmt->bind_param("ssss",$name,$email,$hash,$dept);
            $stmt->execute()?$success="Account created!":$error="Registration failed.";
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Register — HelpDesk OS</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="auth-layout">
<div class="auth-left">
    <div class="auth-brand"><div class="auth-brand-icon">🖥️</div><div><div class="auth-brand-name">HelpDesk OS</div><div class="auth-brand-sub">IT Support Platform</div></div></div>
    <div class="auth-tagline"><h1>Join the <span>Support</span> Platform</h1><p>Create your account and start submitting IT support tickets in minutes.</p></div>
    <div class="auth-feats">
        <div class="auth-feat"><div class="auth-feat-icon">1️⃣</div> Create account with email</div>
        <div class="auth-feat"><div class="auth-feat-icon">2️⃣</div> Submit your first ticket</div>
        <div class="auth-feat"><div class="auth-feat-icon">3️⃣</div> Track real-time status</div>
        <div class="auth-feat"><div class="auth-feat-icon">4️⃣</div> Rate your experience</div>
    </div>
</div>
<div class="auth-right">
<div class="auth-box" style="max-width:460px">
    <h2>Create Account 🚀</h2>
    <p class="auth-sub">Fill in details to get started</p>
    <?php if($error): ?><div class="alert alert-error">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success">✅ <?=$success?> <a href="login.php" style="font-weight:600">Login now →</a></div><?php endif; ?>
    <form method="POST">
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Full Name</label>
            <div class="input-wrap"><span class="iicon">👤</span><input type="text" name="name" required placeholder="Your full name"></div></div>
            <div class="form-group"><label class="form-label">Department</label>
            <div class="input-wrap"><span class="iicon">🏢</span>
            <select name="department"><option value="">Select...</option>
            <?php foreach(['IT','Finance','HR','Operations','Marketing','Engineering','Design','Other'] as $d): ?><option><?=$d?></option><?php endforeach; ?>
            </select></div></div>
            <div class="form-group full"><label class="form-label">Email Address</label>
            <div class="input-wrap"><span class="iicon">✉️</span><input type="email" name="email" required placeholder="you@company.com"></div></div>
            <div class="form-group"><label class="form-label">Password</label>
            <div class="input-wrap"><span class="iicon">🔒</span><input type="password" name="password" required placeholder="Min 6 chars" id="pwd" oninput="checkStrength(this.value)"></div>
            <div class="pwd-strength"><div class="pwd-bar" id="pwd-bar"></div></div>
            <div class="pwd-hint" id="pwd-hint"></div></div>
            <div class="form-group"><label class="form-label">Confirm Password</label>
            <div class="input-wrap"><span class="iicon">🔒</span><input type="password" name="confirm" required placeholder="Repeat password"></div></div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:1.25rem;cursor:pointer"><input type="checkbox" required style="width:auto;padding:0"> I agree to the Terms of Service</label>
        <button type="submit" class="btn btn-primary w-full" style="padding:.75rem">Create Account →</button>
    </form>
    <p style="text-align:center;margin-top:1.25rem;font-size:13px;color:var(--muted)">Already have account? <a href="login.php" style="color:var(--primary);font-weight:600">Sign in</a></p>
</div>
</div>
</div>
<script src="assets/js/main.js"></script></body></html>
