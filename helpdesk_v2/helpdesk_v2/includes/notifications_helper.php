<?php
// includes/notifications_helper.php
// Call this function wherever you want to create a notification

function create_notification($conn, $user_id, $type, $title, $message, $link = '') {
    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)"
    );
    $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
    $stmt->execute();
}

// ── Trigger functions — call these from your PHP files ──

function notify_ticket_created($conn, $ticket_id, $ticket_no, $title, $user_id) {
    // Notify all admins
    $admins = $conn->query("SELECT id FROM users WHERE role='admin'");
    while ($a = $admins->fetch_assoc()) {
        create_notification($conn, $a['id'], 'ticket_created',
            "New Ticket: $ticket_no",
            "\"$title\" has been submitted.",
            "/Helpdesk/admin/dashboard.php"
        );
    }
    // Confirm to user
    create_notification($conn, $user_id, 'ticket_created',
        "Ticket Submitted: $ticket_no",
        "Your ticket \"$title\" has been received.",
        "/Helpdesk/ticket_detail.php?id=$ticket_id"
    );
}

function notify_ticket_updated($conn, $ticket_id, $ticket_no, $title, $new_status, $user_id) {
    create_notification($conn, $user_id, 'ticket_updated',
        "Ticket Updated: $ticket_no",
        "Status changed to \"$new_status\" — $title",
        "/Helpdesk/ticket_detail.php?id=$ticket_id"
    );
}

function notify_ticket_assigned($conn, $ticket_id, $ticket_no, $title, $agent_id) {
    create_notification($conn, $agent_id, 'ticket_assigned',
        "Ticket Assigned: $ticket_no",
        "You have been assigned \"$title\"",
        "/Helpdesk/ticket_detail.php?id=$ticket_id"
    );
}

function notify_ticket_resolved($conn, $ticket_id, $ticket_no, $title, $user_id) {
    create_notification($conn, $user_id, 'ticket_resolved',
        "Ticket Resolved: $ticket_no",
        "\"$title\" has been resolved. Please rate your experience!",
        "/Helpdesk/rate_ticket.php?id=$ticket_id"
    );
}

function notify_sla_breach($conn, $ticket_id, $ticket_no, $title) {
    $admins = $conn->query("SELECT id FROM users WHERE role='admin'");
    while ($a = $admins->fetch_assoc()) {
        create_notification($conn, $a['id'], 'sla_breach',
            "⚠️ SLA Breach: $ticket_no",
            "\"$title\" has breached its SLA deadline!",
            "/Helpdesk/admin/sla_monitor.php"
        );
    }
}

function notify_new_note($conn, $ticket_id, $ticket_no, $user_id, $by_name) {
    create_notification($conn, $user_id, 'new_note',
        "New Note on $ticket_no",
        "$by_name added a note to your ticket.",
        "/Helpdesk/ticket_detail.php?id=$ticket_id"
    );
}
?>
