<?php
// includes/mailer.php — Email notifications via Mailpit (Laragon built-in)
// No PHPMailer needed! Laragon has Mailpit on port 1025

function send_mail($to_email, $to_name, $subject, $body_html) {
    // Using PHP's built-in mail() with Mailpit SMTP
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: HelpDesk OS <helpdesk@localhost>\r\n";
    $headers .= "To: $to_name <$to_email>\r\n";
    return mail($to_email, $subject, $body_html, $headers);
}

function mail_template($title, $body_content, $btn_text = '', $btn_link = '') {
    $btn = $btn_text ? "<a href='$btn_link' style='display:inline-block;background:#2563eb;color:#fff;padding:10px 22px;border-radius:6px;text-decoration:none;font-weight:bold;margin-top:16px'>$btn_text</a>" : '';
    return "
    <div style='font-family:Segoe UI,sans-serif;max-width:560px;margin:0 auto;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden'>
        <div style='background:#1e3a5f;padding:20px 24px;display:flex;align-items:center;gap:10px'>
            <span style='font-size:24px'>🖥️</span>
            <span style='color:#fff;font-size:18px;font-weight:700'>HelpDesk OS</span>
        </div>
        <div style='padding:24px;background:#f9fafb'>
            <h2 style='color:#1e3a5f;margin:0 0 12px'>$title</h2>
            $body_content
            $btn
        </div>
        <div style='background:#e5e7eb;padding:12px 24px;font-size:11px;color:#6b7280;text-align:center'>
            This is an automated email from HelpDesk OS. Do not reply.
        </div>
    </div>";
}

// ── Email Templates ──

function mail_ticket_created($to_email, $to_name, $ticket_no, $title, $priority, $category) {
    $pcolor = ['Critical'=>'#dc2626','High'=>'#f97316','Medium'=>'#2563eb','Low'=>'#16a34a'][$priority] ?? '#6b7280';
    $body = "
        <p style='color:#374151'>Hi <strong>$to_name</strong>,</p>
        <p style='color:#374151'>Your support ticket has been submitted successfully.</p>
        <table style='width:100%;border-collapse:collapse;margin:16px 0;background:#fff;border-radius:6px;overflow:hidden;border:1px solid #e5e7eb'>
            <tr style='background:#f3f4f6'><td style='padding:8px 12px;font-size:12px;color:#6b7280;font-weight:600'>TICKET #</td><td style='padding:8px 12px;font-weight:700;color:#1e3a5f'>$ticket_no</td></tr>
            <tr><td style='padding:8px 12px;font-size:12px;color:#6b7280;font-weight:600'>SUBJECT</td><td style='padding:8px 12px'>$title</td></tr>
            <tr style='background:#f3f4f6'><td style='padding:8px 12px;font-size:12px;color:#6b7280;font-weight:600'>CATEGORY</td><td style='padding:8px 12px'>$category</td></tr>
            <tr><td style='padding:8px 12px;font-size:12px;color:#6b7280;font-weight:600'>PRIORITY</td><td style='padding:8px 12px'><span style='background:$pcolor;color:#fff;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600'>$priority</span></td></tr>
        </table>
        <p style='color:#6b7280;font-size:13px'>You will receive updates when the status changes.</p>";
    $html = mail_template("Ticket Submitted ✅", $body, "View My Tickets", "http://localhost/Helpdesk/my_tickets.php");
    return send_mail($to_email, $to_name, "[$ticket_no] Ticket Submitted — $title", $html);
}

function mail_ticket_updated($to_email, $to_name, $ticket_no, $title, $new_status, $note = '') {
    $scolor = ['Open'=>'#d97706','In Progress'=>'#0891b2','Resolved'=>'#16a34a','Closed'=>'#6b7280'][$new_status] ?? '#6b7280';
    $note_html = $note ? "<div style='background:#eff6ff;border-left:3px solid #2563eb;padding:10px 14px;margin:12px 0;font-size:13px;color:#374151'><strong>Agent Note:</strong> $note</div>" : '';
    $body = "
        <p style='color:#374151'>Hi <strong>$to_name</strong>,</p>
        <p style='color:#374151'>Your ticket <strong>$ticket_no</strong> has been updated.</p>
        <p style='color:#374151'>New Status: <span style='background:$scolor;color:#fff;padding:3px 12px;border-radius:99px;font-size:12px;font-weight:600'>$new_status</span></p>
        $note_html";
    $html = mail_template("Ticket Updated — $ticket_no", $body, "View Ticket", "http://localhost/Helpdesk/my_tickets.php");
    return send_mail($to_email, $to_name, "[$ticket_no] Status Updated: $new_status", $html);
}

function mail_ticket_resolved($to_email, $to_name, $ticket_no, $title, $ticket_id) {
    $body = "
        <p style='color:#374151'>Hi <strong>$to_name</strong>,</p>
        <p style='color:#374151'>Great news! Your ticket <strong>$ticket_no — $title</strong> has been <span style='color:#16a34a;font-weight:700'>Resolved</span>.</p>
        <p style='color:#374151'>Please take a moment to rate your support experience.</p>
        <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px;margin:12px 0;text-align:center;font-size:24px'>⭐⭐⭐⭐⭐</div>";
    $html = mail_template("Ticket Resolved ✅", $body, "Rate Your Experience", "http://localhost/Helpdesk/rate_ticket.php?id=$ticket_id");
    return send_mail($to_email, $to_name, "[$ticket_no] Resolved — Please Rate Your Experience", $html);
}

function mail_sla_alert($admin_email, $ticket_no, $title, $priority) {
    $body = "
        <p style='color:#374151'>This is an urgent SLA breach alert.</p>
        <div style='background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;padding:14px;margin:12px 0'>
            <p style='color:#dc2626;font-weight:700;margin:0'>⚠️ Ticket $ticket_no has breached its SLA!</p>
            <p style='color:#374151;margin:6px 0 0'>Subject: $title</p>
            <p style='color:#374151;margin:4px 0 0'>Priority: $priority</p>
        </div>
        <p style='color:#6b7280;font-size:13px'>Please assign or escalate this ticket immediately.</p>";
    $html = mail_template("⚠️ SLA Breach Alert", $body, "Go to Admin Panel", "http://localhost/Helpdesk/admin/sla_monitor.php");
    return send_mail($admin_email, 'Admin', "🚨 SLA BREACH: $ticket_no — Immediate Action Required", $html);
}

function mail_otp($to_email, $to_name, $otp) {
    $body = "
        <p style='color:#374151'>Hi <strong>$to_name</strong>,</p>
        <p style='color:#374151'>Your one-time login code is:</p>
        <div style='text-align:center;margin:20px 0'>
            <span style='font-size:36px;font-weight:700;letter-spacing:10px;color:#1e3a5f;background:#f0f9ff;padding:12px 24px;border-radius:8px;border:2px dashed #2563eb'>$otp</span>
        </div>
        <p style='color:#6b7280;font-size:13px;text-align:center'>⏱️ This code expires in <strong>10 minutes</strong>. Do not share it.</p>";
    $html = mail_template("Your Login OTP 🔐", $body);
    return send_mail($to_email, $to_name, "HelpDesk Login OTP: $otp", $html);
}
?>
