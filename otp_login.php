<?php
session_start();
require_once 'includes/db.php';

$step  = $_GET['step'] ?? '1';
$error = $success = '';

// STEP 1 — Email maango
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '1') {
    $email = trim($_POST['email'] ?? '');

    // Check user exists
    $chk = $conn->prepare("SELECT id, name FROM users WHERE email=?");
    $chk->bind_param("s", $email);
    $chk->execute();
    $user = $chk->get_result()->fetch_assoc();

    if (!$user) {
        $error = "You email is not registered";
    } else {
        // OTP generate karo
        $otp     = rand(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Purana OTP delete karo
        $del = $conn->prepare("DELETE FROM otp_codes WHERE email=?");
        $del->bind_param("s", $email);
        $del->execute();

        // Naya OTP save karo
        $ins = $conn->prepare("INSERT INTO otp_codes (email, otp, expires_at) VALUES (?,?,?)");
        $ins->bind_param("sss", $email, $otp, $expires);
        $ins->execute();

        // Email bhejo — Mailpit se
        $to      = $email;
        $subject = "HelpDesk Login OTP";
        $message = "Your OTP is: $otp\nValid for 10 minutes.";
        $headers = "From: helpdesk@localhost";
        mail($to, $subject, $message, $headers);

        $_SESSION['otp_email'] = $email;
        $success = "OTP sent — $email Check your otp on registered email!";
        $step = '2';
    }
}

// STEP 2 — OTP verify karo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp   = trim($_POST['otp'] ?? '');
    $email = $_SESSION['otp_email'] ?? '';

    $chk = $conn->prepare("SELECT * FROM otp_codes WHERE email=? AND otp=? AND expires_at > NOW()");
    $chk->bind_param("ss", $email, $otp);
    $chk->execute();
    $valid = $chk->get_result()->fetch_assoc();

    if (!$valid) {
        $error = "OTP expired or invalid!";
        $step  = '2';
    } else {
        // OTP delete karo
        $conn->query("DELETE FROM otp_codes WHERE email='$email'");

        // User login karo
        $usr = $conn->prepare("SELECT id, name, role FROM users WHERE email=?");
        $usr->bind_param("s", $email);
        $usr->execute();
        $user = $usr->get_result()->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Login — HelpDesk</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .otp-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .otp-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            color: #1d4ed8;
            margin-bottom: 1rem;
            text-align: center;
        }
        .timer {
            text-align: center;
            font-size: 13px;
            color: #dc2626;
            margin-top: .5rem;
        }
    </style>
</head>
<body class="auth-page">
<div class="auth-box">
    <h2>🖥️ HelpDesk OS</h2>

    <?php if ($step === '1' && !$success): ?>
    <!-- STEP 1 — Email form -->
    <p class="auth-sub">Login with otp</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="?step=1">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@company.com" autofocus>
        </div>
        <button type="submit" class="btn-primary w-full">Send OTP 📧</button>
    </form>

    <p style="margin-top:1rem;font-size:13px;text-align:center">
        Login with Password? <a href="login.php">Login</a>
    </p>

    <?php elseif ($step === '2' || $success): ?>
    <!-- STEP 2 — OTP form -->
    <p class="auth-sub">Enter OTP</p>

    <div class="otp-info">
        📧 OTP SENT:<br>
        <strong><?= htmlspecialchars($_SESSION['otp_email'] ?? '') ?></strong>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="?step=2">
        <div class="form-group">
            <label>6-Digit OTP</label>
            <input type="text" name="otp" required
                   placeholder="000000"
                   maxlength="6"
                   class="otp-input"
                   autofocus
                   autocomplete="off">
        </div>
        <button type="submit" class="btn-primary w-full">Verify OTP ✅</button>
    </form>

    <!-- Timer -->
    <div class="timer" id="timer">⏱ OTP expires in: <span id="countdown">10:00</span></div>

    <p style="margin-top:1rem;font-size:13px;text-align:center">
        OTP nahi aaya? <a href="otp_login.php">Dobara bhejo</a>
    </p>

    <?php endif; ?>
</div>

<script>
// Countdown timer — 10 minutes
let seconds = 600;
const el = document.getElementById('countdown');
if (el) {
    const timer = setInterval(() => {
        seconds--;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        if (seconds <= 0) {
            clearInterval(timer);
            el.textContent = 'Expired!';
            el.style.color = '#dc2626';
        }
    }, 1000);
}
</script>
</body>
</html>