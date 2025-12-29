<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "not_logged_in"]);
    exit();
}

$post_id = intval($_POST['post_id']);
$user_id = intval($_SESSION['user_id']);
$VOTE_LIMIT = 5;

$getOwner = mysqli_query($conn, "SELECT user_id FROM cars WHERE id=$post_id LIMIT 1");
$owner = mysqli_fetch_assoc($getOwner);

if ($owner && $owner['user_id'] == $user_id) {
    echo json_encode([
        "status" => "blocked",
        "message" => "You cannot vote for your own post."
    ]);
    exit();
}

$exists = mysqli_query($conn, 
    "SELECT id FROM votes WHERE post_id=$post_id AND user_id=$user_id LIMIT 1"
);

if (mysqli_num_rows($exists) > 0) {
    mysqli_query($conn, "DELETE FROM votes WHERE post_id=$post_id AND user_id=$user_id");
    $action = "unvoted";
} else {

    $totalVotesQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM votes WHERE user_id=$user_id");
    $totalVotesRow = mysqli_fetch_assoc($totalVotesQuery);
    $currentTotal = $totalVotesRow['total'];

    if ($currentTotal >= $VOTE_LIMIT) {
        echo json_encode([
            "status" => "limit_reached",
            "message" => "You have reached your limit of $VOTE_LIMIT votes."
        ]);
        exit();
    }

    mysqli_query($conn, "INSERT INTO votes (post_id, user_id) VALUES ($post_id, $user_id)");
    $action = "voted";
}

$countQuery = mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM votes WHERE post_id=$post_id"
);
$countRow = mysqli_fetch_assoc($countQuery);
$voteCount = $countRow['total'] ?? 0;

echo json_encode([
    "status" => "success",
    "action" => $action,
    "vote_count" => $voteCount
]);
?>