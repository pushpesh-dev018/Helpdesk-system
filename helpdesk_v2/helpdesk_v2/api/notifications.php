<?php
// api/notifications.php — Live notification API
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode([]); exit(); }

$uid    = current_user_id();
$role   = current_role();
$action = $_GET['action'] ?? 'list';

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff/60) . ' min ago';
    if ($diff < 86400)  return floor($diff/3600) . ' hr ago';
    return floor($diff/86400) . ' days ago';
}

function notif_icon($type) {
    $map = [
        'ticket_created'  => '🎫',
        'ticket_updated'  => '✏️',
        'ticket_assigned' => '👤',
        'ticket_resolved' => '✅',
        'sla_breach'      => '⚠️',
        'new_note'        => '💬',
        'ticket_rated'    => '⭐',
    ];
    return $map[$type] ?? '🔔';
}

switch ($action) {
    case 'list':
        $where = $role === 'admin' ? "1=1" : "n.user_id = $uid";
        $notifs = $conn->query(
            "SELECT n.* FROM notifications n WHERE $where ORDER BY n.created_at DESC LIMIT 15"
        );
        $result = [];
        while ($r = $notifs->fetch_assoc()) {
            $result[] = [
                'id'       => $r['id'],
                'title'    => $r['title'],
                'message'  => $r['message'],
                'type'     => $r['type'],
                'icon'     => notif_icon($r['type']),
                'link'     => $r['link'] ?? '',
                'is_read'  => $r['is_read'],
                'time_ago' => time_ago($r['created_at']),
            ];
        }
        echo json_encode($result);
        break;

    case 'count':
        $where  = $role === 'admin' ? "is_read=0" : "user_id=$uid AND is_read=0";
        $count  = $conn->query("SELECT COUNT(*) c FROM notifications WHERE $where")->fetch_assoc()['c'];
        $latest = '';
        if ($count > 0) {
            $lw  = $role === 'admin' ? "is_read=0" : "user_id=$uid AND is_read=0";
            $row = $conn->query("SELECT title FROM notifications WHERE $lw ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
            $latest = $row['title'] ?? '';
        }
        echo json_encode(['count' => (int)$count, 'latest' => $latest]);
        break;

    case 'read':
        $id   = intval($_GET['id'] ?? 0);
        $conn->query("UPDATE notifications SET is_read=1 WHERE id=$id");
        echo json_encode(['ok' => true]);
        break;

    case 'read_all':
        $where = $role === 'admin' ? "1=1" : "user_id=$uid";
        $conn->query("UPDATE notifications SET is_read=1 WHERE $where");
        echo json_encode(['ok' => true]);
        break;
}
