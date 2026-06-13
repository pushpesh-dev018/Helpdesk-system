# 🖥️ IT Helpdesk Ticketing System

A web-based IT Helpdesk and Ticketing System built as a college project to learn full-stack web development. This system helps manage IT support requests from submission to resolution.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)

> 💡 **Note:** This is my first full-stack project. I built this to understand how real-world IT support systems work.

---

## ✨ What This Project Does

Imagine you work in a company and your laptop stops working. You need to report it to the IT team. This system lets you:

- **Submit a complaint (ticket)** with details about your problem
- **Track the status** — Open → In Progress → Resolved
- **Get notified** when your ticket is updated
- **Rate the support** you received

And the IT Admin can:
- **See all tickets** in one dashboard
- **Assign tickets** to team members
- **Monitor SLA** (time limit to solve tickets)
- **View reports** and analytics

---

## 🚀 Features

### For Users
- ✅ Register and Login (Email/Password or OTP)
- ✅ Submit support tickets with category and priority
- ✅ Attach screenshots or files
- ✅ Track ticket status in real-time
- ✅ View activity log and agent notes
- ✅ Rate support experience (1-5 stars)
- ✅ Forgot password / Reset password

### For Admin
- ✅ Dashboard with live stats
- ✅ View and manage all tickets
- ✅ Assign tickets to agents
- ✅ SLA breach monitoring and alerts
- ✅ Analytics charts (Chart.js)
- ✅ Export tickets to CSV
- ✅ Manage users
- ✅ 30+ Canned responses
- ✅ Full audit log
- ✅ Live notifications

### Security
- ✅ bcrypt password hashing
- ✅ OTP login (10-minute expiry)
- ✅ Role-based access control
- ✅ SQL injection prevention
- ✅ Session management

---

## 🛠️ Tech Stack

| Technology | Purpose |
|---|---|
| PHP 8.x | Backend logic |
| MySQL 8.x | Database |
| HTML5 / CSS3 | Frontend UI |
| JavaScript | Interactivity |
| Chart.js | Analytics graphs |
| Laragon | Local development |

---

## 📁 Project Structure

```
helpdesk/
├── index.php              — Dashboard
├── login.php              — Login page
├── register.php           — Register
├── otp_login.php          — OTP login
├── logout.php             — Logout
├── submit_ticket.php      — New ticket
├── my_tickets.php         — My tickets
├── ticket_detail.php      — Ticket view
├── rate_ticket.php        — Rating
├── search.php             — Search
├── notifications.php      — Notifications
├── password_reset.php     — Reset password
├── database.sql           — DB schema
├── database_updates.sql   — Extra tables
│
├── admin/
│   ├── dashboard.php      — Admin panel
│   ├── update_ticket.php  — Edit ticket
│   ├── manage_users.php   — Users
│   ├── reports.php        — Charts + CSV
│   ├── audit_log.php      — Audit trail
│   ├── canned_responses.php
│   └── sla_monitor.php    — SLA tracker
│
├── includes/
│   ├── db.php             — DB connection
│   ├── auth.php           — Auth helpers
│   ├── header.php         — Sidebar nav
│   ├── footer.php         — Footer
│   └── mailer.php         — Emails
│
├── api/
│   └── notifications.php  — Notif API
│
├── assets/
│   ├── css/style.css      — Styling
│   └── js/main.js         — JS
│
└── uploads/               — File uploads
```

### What You Need
- Laragon — https://laragon.org/download
- VS Code — https://code.visualstudio.com
- Web Browser (Chrome/Firefox)

---

### Step 8 — Login
| Role | Email | Password |
|---|---|---|
| Admin | admin@helpdesk.com | password123 |
| User | rahul@company.com | password123 |

---

## 🌐 Deploy Online — Free Hosting

### InfinityFree (Recommended)

**Step 1 — Sign Up**
👉 https://infinityfree.com → Create free account

**Step 2 — Create Hosting**
1. Login → Click **"Create Account"**
2. Choose subdomain: `myhelpdesk.infinityfreeapp.com`
3. Click **Create**

**Step 3 — Create Database**
1. Control Panel → **MySQL Databases**
2. Create database → Note down:
   - Host (e.g. `sql200.infinityfree.com`)
   - Database name
   - Username
   - Password

**Step 4 — Update db.php**
```php
define('DB_HOST', 'sql200.infinityfree.com');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_pass');
define('DB_NAME', 'your_db_name');
```

**Step 5 — Import Database**
1. Control Panel → **phpMyAdmin**
2. Select your database → **Import**
3. Upload `database.sql` → Go
4. Upload `database_updates.sql` → Go

**Step 6 — Upload Files**

**Option A — File Manager:**
1. Control Panel → **File Manager**
2. Open `htdocs` folder
3. Upload all project files

**Option B — FileZilla FTP:**
1. Download FileZilla — https://filezilla-project.org
2. Host: `ftpupload.net`
3. Username/Password: from InfinityFree panel
4. Upload all files to `/htdocs/`

**Step 7 — Visit Live Site**
```
http://myhelpdesk.infinityfreeapp.com/login.php
```

---

## 📊 SLA Policy

| Priority | Time Limit |
|---|---|
| 🔴 Critical | 4 hours |
| 🟠 High | 8 hours |
| 🟡 Medium | 24 hours |
| 🟢 Low | 72 hours |

---


## 👨‍💻 Author

**Your Name**
- 🎓 [Shri Ramswaroop Memorial University]
- 📧 pushpesh018@gmail.com
- 🐙 [GitHub]()

---

> ⭐ If this helped you, please give it a star on GitHub!
