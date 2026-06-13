<?php
// includes/audit.php — Audit log helper

function audit_log($conn, $user_id, $action, $target_type = '', $target_id = 0, $details = '') {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, target_type, target_id, details, ip_address)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param("ississ", $user_id, $action, $target_type, $target_id, $details, $ip);
    $stmt->execute();
}

// Usage examples:
// audit_log($conn, $uid, 'ticket_created',  'ticket', $ticket_id, "TKT-0001 created");
// audit_log($conn, $uid, 'ticket_updated',  'ticket', $ticket_id, "Status changed to Resolved");
// audit_log($conn, $uid, 'user_login',      'user',   $uid,       "Login from $ip");
// audit_log($conn, $uid, 'ticket_deleted',  'ticket', $ticket_id, "TKT-0004 deleted by admin");
?>
