<?php
$conn = new mysqli('localhost', 'root', 'Pushpa4441@', 'helpdesk_db');
$result = $conn->query("SELECT password FROM users WHERE email='admin@helpdesk.com'");
$user = $result->fetch_assoc();
echo password_verify('password123', $user['password']) ? "✅ Correct" : "❌ Wrong";
?>