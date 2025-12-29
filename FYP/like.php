<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "not_logged_in"]);
    exit();
}

$post_id = intval($_POST['post_id']);
$user_id = intval($_SESSION['user_id']);

$exists = mysqli_query($conn, "SELECT * FROM likes WHERE post_id=$post_id AND user_id=$user_id");

if (mysqli_num_rows($exists) > 0) {
    mysqli_query($conn, "DELETE FROM likes WHERE post_id=$post_id AND user_id=$user_id");
    $action = "unliked";
} else {
    mysqli_query($conn, "INSERT INTO likes (post_id,user_id) VALUES ($post_id,$user_id)");
    $action = "liked";
}

$count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM likes WHERE post_id=$post_id"))[0];

echo json_encode([
    "status" => "success",
    "action" => $action,
    "like_count" => $count
]);
