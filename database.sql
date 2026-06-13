-- ============================================================
--  IT Helpdesk Ticketing System — Database Schema
--  Run: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS helpdesk_db;
USE helpdesk_db;

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('user','admin') DEFAULT 'user',
    department  VARCHAR(100),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ticket_no    VARCHAR(20) NOT NULL UNIQUE,
    user_id      INT NOT NULL,
    assigned_to  INT DEFAULT NULL,
    title        VARCHAR(255) NOT NULL,
    description  TEXT NOT NULL,
    category     ENUM('Hardware','Software','Network','Access','Email','Security','Other') NOT NULL,
    priority     ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
    status       ENUM('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
    sla_deadline DATETIME,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE ticket_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT NOT NULL,
    user_id    INT NOT NULL,
    note       TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- Seed: password for all = password123
INSERT INTO users (name, email, password, role, department) VALUES
('Admin User',   'admin@helpdesk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'IT'),
('Rahul Sharma', 'rahul@company.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',  'Finance'),
('Priya Nair',   'priya@company.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',  'HR');

INSERT INTO tickets (ticket_no, user_id, assigned_to, title, description, category, priority, status, sla_deadline) VALUES
('TKT-0001', 2, 1, 'Laptop not starting',  'Laptop shows black screen on power on.', 'Hardware', 'Critical', 'Open',        DATE_ADD(NOW(), INTERVAL 4  HOUR)),
('TKT-0002', 3, 1, 'Cannot access VPN',    'VPN throws error 800 from home.',        'Network',  'High',     'In Progress', DATE_ADD(NOW(), INTERVAL 8  HOUR)),
('TKT-0003', 2, 1, 'Outlook not syncing',  'Emails not loading after migration.',    'Email',    'Medium',   'Open',        DATE_ADD(NOW(), INTERVAL 24 HOUR));

INSERT INTO ticket_logs (ticket_id, user_id, note) VALUES
(1, 1, 'Ticket received. Assigning to hardware team.'),
(2, 1, 'Checking firewall rules and VPN config.');
