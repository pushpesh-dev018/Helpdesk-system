# Mobile Responsive + Email + Live Notifications — Setup Guide

## STEP 1 — Database update karo
HeidiSQL mein `helpdesk_db` select karo → File → Run SQL File → `database_v2.sql` select karo → Open

## STEP 2 — Files copy karo apne Helpdesk folder mein

### Replace karo (existing files):
| Source file | Copy to |
|---|---|
| assets/css/style.css | C:\laragon\www\Helpdesk\assets\css\style.css |
| assets/js/main.js | C:\laragon\www\Helpdesk\assets\js\main.js |
| includes/header.php | C:\laragon\www\Helpdesk\includes\header.php |
| includes/mailer.php | C:\laragon\www\Helpdesk\includes\mailer.php |

### Naye files add karo:
| Source file | Copy to |
|---|---|
| includes/notifications_helper.php | C:\laragon\www\Helpdesk\includes\notifications_helper.php |
| api/notifications.php | C:\laragon\www\Helpdesk\api\notifications.php |
| notifications.php | C:\laragon\www\Helpdesk\notifications.php |

## STEP 3 — api folder banao
C:\laragon\www\Helpdesk\ mein naya folder banao naam: `api`
Phir api/notifications.php wahan copy karo

## STEP 4 — Email test karo (Mailpit)
Browser mein jao: http://localhost:8025
Yahan saare sent emails dikhenge — PHPMailer ki zaroorat nahi!

## STEP 5 — Notifications trigger karo
submit_ticket.php mein yeh add karo upar:

    require_once 'includes/notifications_helper.php';

Aur ticket submit hone ke baad:

    notify_ticket_created($conn, $new_id, $ticket_no, $title, $uid);
    mail_ticket_created($user_email, $user_name, $ticket_no, $title, $priority, $category);

admin/update_ticket.php mein status change pe:

    notify_ticket_updated($conn, $id, $ticket_no, $title, $status, $ticket['user_id']);
    mail_ticket_updated($user_email, $user_name, $ticket_no, $title, $status, $note);

## Features Added:
- Mobile responsive navbar with hamburger menu
- Live notification bell with unread count badge
- Real-time polling every 30 seconds
- Toast popup for new notifications
- Full notifications page
- Email notifications via Mailpit (built into Laragon)
- Beautiful HTML email templates
