-- ============================================================
--  database_updates.sql
--  Existing helpdesk_db mein yeh tables ADD karo
--  Run: mysql -u root -p helpdesk_db < database_updates.sql
-- ============================================================

USE helpdesk_db;

-- 1. File Attachments
CREATE TABLE IF NOT EXISTS ticket_attachments (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id     INT NOT NULL,
    user_id       INT NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- 2. Password Reset Tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token      VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. CSAT Ticket Ratings
CREATE TABLE IF NOT EXISTS ticket_ratings (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id   INT NOT NULL,
    rating    TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment   TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY one_rating (ticket_id, user_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- 4. Audit Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    action      VARCHAR(100) NOT NULL,
    target_type VARCHAR(50)  DEFAULT '',
    target_id   INT          DEFAULT 0,
    details     TEXT,
    ip_address  VARCHAR(45)  DEFAULT '',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Canned Responses
CREATE TABLE IF NOT EXISTS canned_responses (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150) NOT NULL,
    body       TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample canned responses
INSERT IGNORE INTO canned_responses (title, body, created_by) VALUES
('Hardware — Restart Steps',     'Please try the following steps:\n1. Shut down the system completely\n2. Unplug for 30 seconds\n3. Power on and check\nIf issue persists, we will schedule an onsite visit.', 1),
('Network — VPN Troubleshoot',   'To fix VPN connectivity:\n1. Restart the VPN client\n2. Check your internet connection\n3. Try connecting on a different network\n4. Reinstall the VPN client if needed', 1),
('Software — Reinstall Guide',   'Please try reinstalling the software:\n1. Uninstall via Control Panel\n2. Restart your system\n3. Download fresh installer from IT portal\n4. Install and test again', 1),
('Ticket Resolved — Feedback',   'Your ticket has been resolved. Please let us know if the issue recurs. We value your feedback — kindly rate your experience!', 1);
