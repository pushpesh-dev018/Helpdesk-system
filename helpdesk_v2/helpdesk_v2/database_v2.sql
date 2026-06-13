-- =====================================================
--  database_v2.sql — Run this in HeidiSQL
--  helpdesk_db select karke F9 dabao
-- =====================================================

USE helpdesk_db;

-- Notifications table (live bell icon ke liye)
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    title      VARCHAR(200) NOT NULL,
    message    TEXT,
    link       VARCHAR(255) DEFAULT '',
    is_read    TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample notifications
INSERT INTO notifications (user_id, type, title, message, link) VALUES
(1, 'ticket_created',  'New Ticket: TKT-0001', 'Laptop not starting has been submitted.', '/Helpdesk/admin/dashboard.php'),
(1, 'sla_breach',      '⚠️ SLA Breach: TKT-0001', 'Critical ticket has breached SLA!', '/Helpdesk/admin/sla_monitor.php'),
(2, 'ticket_updated',  'Ticket Updated: TKT-0001', 'Status changed to In Progress', '/Helpdesk/ticket_detail.php?id=1'),
(2, 'ticket_resolved', 'Ticket Resolved: TKT-0002', 'Your VPN issue has been resolved!', '/Helpdesk/rate_ticket.php?id=2');
