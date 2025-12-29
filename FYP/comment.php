<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status"=>"not_logged_in"]);
    exit();
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$comment = trim($_POST['comment']);

if ($comment != "") {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    $stmt->execute();
}

echo json_encode(["status"=>"success"]);
?>
