<?php
// includes/file_upload.php — File attachment handler

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg','image/png','image/gif','application/pdf','text/plain','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

function handle_upload($file) {
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if ($file['error'] !== UPLOAD_ERR_OK)       return ['ok'=>false,'msg'=>'Upload error.'];
    if ($file['size'] > MAX_FILE_SIZE)           return ['ok'=>false,'msg'=>'File too large. Max 5MB.'];
    if (!in_array($file['type'], ALLOWED_TYPES)) return ['ok'=>false,'msg'=>'File type not allowed.'];

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('attach_') . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok'=>true,'filename'=>$filename,'original'=>$file['name'],'size'=>$file['size']];
    }
    return ['ok'=>false,'msg'=>'Failed to save file.'];
}

function get_ticket_attachments($conn, $ticket_id) {
    $stmt = $conn->prepare("SELECT * FROM ticket_attachments WHERE ticket_id=? ORDER BY created_at DESC");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function save_attachment_db($conn, $ticket_id, $user_id, $filename, $original) {
    $stmt = $conn->prepare("INSERT INTO ticket_attachments (ticket_id, user_id, filename, original_name) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $ticket_id, $user_id, $filename, $original);
    return $stmt->execute();
}
?>
