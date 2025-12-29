<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "not_logged_in"]);
    exit();
}

$comment_id = intval($_POST['comment_id']);

$sql = "
    SELECT c.id, c.post_id, p.user_id AS post_owner
    FROM comments c
    JOIN cars p ON c.post_id = p.id
    WHERE c.id = $comment_id
";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Comment not found."]);
    exit();
}

if ($_SESSION['user_id'] != $data['post_owner']) {
    echo json_encode(["status" => "error", "message" => "You are not allowed to delete this comment."]);
    exit();
}

mysqli_query($conn, "DELETE FROM comments WHERE id = $comment_id");

echo json_encode(["status" => "success"]);
?>
