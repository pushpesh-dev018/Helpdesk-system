<?php
// includes/canned_responses.php — Preset replies for agents

function get_canned_responses($conn) {
    return $conn->query("SELECT * FROM canned_responses ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
}

function add_canned_response($conn, $title, $body, $created_by) {
    $stmt = $conn->prepare("INSERT INTO canned_responses (title, body, created_by) VALUES (?,?,?)");
    $stmt->bind_param("ssi", $title, $body, $created_by);
    return $stmt->execute();
}

function delete_canned_response($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM canned_responses WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
