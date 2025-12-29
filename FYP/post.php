<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) die("Invalid post");

$post_id = (int)$_GET['id'];

$postQ = mysqli_query($conn, "SELECT * FROM cars WHERE id=$post_id AND approval_status='Approved'");
if (!$postQ || mysqli_num_rows($postQ) == 0) die("Post not found.");
$post = mysqli_fetch_assoc($postQ);

$post_owner_id = (int)$post['user_id'];
$user_id = (int)($_SESSION['user_id'] ?? 0);
$isOwner = ($user_id > 0 && $user_id === $post_owner_id);

$message = "";

if ($isOwner && isset($_POST['update_desc'])) {
    $newDesc = trim($_POST['description'] ?? "");
    if (strlen($newDesc) > 2000) {
        $message = "Description too long (max 2000 characters).";
    } else {
        $stmt = $conn->prepare("UPDATE cars SET description=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sii", $newDesc, $post_id, $user_id);
        if ($stmt->execute()) {
            $post['description'] = $newDesc;
            $message = "Description updated successfully!";
        } else {
            $message = "Failed to update description.";
        }
        $stmt->close();
    }
}

$cover = trim($post['image_path'] ?? "");

$imageList = [];
$imgRes = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id=$post_id ORDER BY id ASC");
if ($imgRes && mysqli_num_rows($imgRes) > 0) {
    while ($r = mysqli_fetch_assoc($imgRes)) {
        if (!empty($r['image_path'])) $imageList[] = $r['image_path'];
    }
}

if ($cover !== "" && !empty($imageList)) {
    $idx = array_search($cover, $imageList, true);
    if ($idx !== false) {
        unset($imageList[$idx]);
        array_unshift($imageList, $cover);
        $imageList = array_values($imageList);
    } else {
        array_unshift($imageList, $cover);
    }
}

if (empty($imageList)) {
    $imageList = ["noimage.png"];
}

$comments = mysqli_query($conn, "
    SELECT c.id,c.comment,c.user_id,u.username
    FROM comments c JOIN users u ON c.user_id=u.id
    WHERE c.post_id=$post_id
    ORDER BY c.created_at DESC
");

$like_count = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM likes WHERE post_id=$post_id"))[0];
$vote_count = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM votes WHERE post_id=$post_id"))[0];

$user_liked = 0;
$user_voted = 0;

if ($user_id) {
    $user_liked = mysqli_fetch_row(mysqli_query($conn,
        "SELECT COUNT(*) FROM likes WHERE post_id=$post_id AND user_id=$user_id"
    ))[0];

    $user_voted = mysqli_fetch_row(mysqli_query($conn,
        "SELECT COUNT(*) FROM votes WHERE post_id=$post_id AND user_id=$user_id"
    ))[0];
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($post['car_name']) ?> | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    background:#0b0b0f;
    color:#eaeaea;
    font-family:Arial;
    margin:0;
}

.container {
    max-width:900px;
    margin:40px auto;
    padding:20px;
}

.post-card {
    background:#111;
    border-radius:18px;
    padding:26px;
    box-shadow:0 0 40px rgba(0,0,0,.7);
    border:1px solid #1f1f2a;
}

.post-title {
    font-size:34px;
    background:linear-gradient(90deg,#b35cff,#6f9dff);
    -webkit-background-clip:text;
    color:transparent;
    margin:0;
}
.post-author {
    color:#aaa;
    margin-top:6px;
    margin-bottom:18px;
}

.msg {
    background:#151521;
    border:1px solid rgba(255,255,255,0.08);
    padding:10px 12px;
    border-radius:12px;
    color:#9ec9ff;
    margin:14px 0 18px;
    font-size:14px;
}

.slider {
    background:#000;
    border-radius:16px;
    overflow:hidden;
    position:relative;
}
.slide {
    width:100%;
    height:420px;
    display:none;
    object-fit:contain;
    background:#000;
}
.slide.active { display:block; }

.nav {
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background:rgba(0,0,0,.6);
    border:none;
    color:white;
    font-size:20px;
    padding:12px;
    cursor:pointer;
    border-radius:50%;
}
.prev { left:14px; }
.next { right:14px; }

.stats {
    display:flex;
    gap:30px;
    margin:18px 0;
    color:#ccc;
    font-size:15px;
}

.desc {
    background:#0f0f14;
    border-radius:14px;
    padding:18px;
    margin-top:16px;
    border:1px solid rgba(255,255,255,0.06);
}
.desc-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
}
.desc h3 {
    color:#b35cff;
    margin:0;
}
.desc p {
    color:#ddd;
    line-height:1.6;
    margin:0;
    white-space:pre-wrap;
}

.edit-btn {
    background:linear-gradient(90deg,#7b4bff,#b35cff);
    border:none;
    color:white;
    padding:8px 12px;
    border-radius:10px;
    cursor:pointer;
    font-size:13px;
}
.edit-btn:hover { opacity:0.9; }

.edit-area {
    width:100%;
    min-height:120px;
    background:#0b0b10;
    border:1px solid rgba(255,255,255,0.10);
    border-radius:12px;
    padding:12px;
    color:#fff;
    resize:vertical;
    font-size:14px;
    line-height:1.5;
}
.edit-actions{
    display:flex;
    gap:10px;
    margin-top:10px;
}
.save-btn, .cancel-btn {
    flex:1;
    padding:12px;
    border-radius:12px;
    border:none;
    cursor:pointer;
    font-weight:bold;
}
.save-btn{
    background:#1db954;
    color:#fff;
}
.cancel-btn{
    background:#1f1f2a;
    color:#fff;
}

.actions {
    margin-top:20px;
}
.btn {
    width:100%;
    padding:14px;
    border-radius:12px;
    border:none;
    background:#1f1f2a;
    color:white;
    cursor:pointer;
    margin-top:10px;
    transition:.25s;
}
.btn:hover { background:#2a2a38; }
.btn-active {
    background:#b35cff;
    box-shadow:0 0 16px rgba(179,92,255,.6);
}

.comments {
    margin-top:32px;
}
.comment {
    background:#0f0f14;
    border-radius:14px;
    padding:14px;
    margin-top:12px;
    position:relative;
    border:1px solid rgba(255,255,255,0.06);
}
.comment small { color:#aaa; }

.comment-input {
    width:100%;
    height:90px;
    background:#0f0f14;
    border:none;
    border-radius:12px;
    padding:12px;
    color:white;
    margin-top:16px;
}

.delete-btn {
    position:absolute;
    top:12px;
    right:12px;
    background:#ff4d4d;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    color:white;
    cursor:pointer;
    font-size:12px;
}
</style>

<script>
function send(url,data){
    return fetch(url,{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:new URLSearchParams(data)
    }).then(r=>r.json());
}

function likePost(id){
    send("like.php",{post_id:id}).then(r=>{
        if(r.status==="not_logged_in") return alert("Login required");
        document.getElementById("likeCount").innerText=r.like_count;
        document.getElementById("likeBtn").classList.toggle("btn-active",r.action==="liked");
    });
}

function votePost(id){
    send("vote.php",{post_id:id}).then(r=>{
        if(r.status==="not_logged_in") return alert("Login required");
        if(r.status==="blocked") return alert("Cannot vote own post");
        document.getElementById("voteCount").innerText=r.vote_count;
        document.getElementById("voteBtn").classList.toggle("btn-active",r.action==="voted");
    });
}

function addComment(id){
    let c=document.getElementById("commentInput").value.trim();
    if(!c) return;
    send("comment.php",{post_id:id,comment:c}).then(()=>location.reload());
}

function delComment(id){
    if(!confirm("Delete comment?")) return;
    send("delete_comment.php",{comment_id:id}).then(()=>location.reload());
}

function toggleEditDesc(show){
    const viewBox = document.getElementById("descView");
    const editBox = document.getElementById("descEdit");
    if(!viewBox || !editBox) return;

    if (show){
        viewBox.style.display = "none";
        editBox.style.display = "block";
        const ta = document.getElementById("descTextarea");
        if (ta) ta.focus();
    } else {
        editBox.style.display = "none";
        viewBox.style.display = "block";
    }
}
</script>
</head>

<body>

<?php include "header.php"; ?>

<div class="container">
<div class="post-card">

    <h1 class="post-title"><?= htmlspecialchars($post['car_name']) ?></h1>
    <div class="post-author">By @<?= htmlspecialchars($post['username']) ?></div>

    <?php if (!empty($message)): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="slider">
        <?php
        $countSlides = count($imageList);
        foreach ($imageList as $k => $path):
            $active = ($k === 0) ? "active" : "";
        ?>
            <img class="slide <?= $active ?>" src="<?= htmlspecialchars($path) ?>" alt="car image">
        <?php endforeach; ?>

        <?php if ($countSlides > 1): ?>
            <button class="nav prev" onclick="prev()">‹</button>
            <button class="nav next" onclick="next()">›</button>
        <?php endif; ?>
    </div>

    <div class="stats">
        ❤️ <span id="likeCount"><?= (int)$like_count ?></span>
        ⭐ <span id="voteCount"><?= (int)$vote_count ?></span>
    </div>
    
    <div class="desc">
        <div class="desc-head">
            <h3>Description</h3>

            <?php if ($isOwner): ?>
                <button class="edit-btn" type="button" onclick="toggleEditDesc(true)">Edit</button>
            <?php endif; ?>
        </div>

        <div id="descView">
            <?php if (!empty(trim($post['description'] ?? ""))): ?>
                <p><?= htmlspecialchars($post['description']) ?></p>
            <?php else: ?>
                <p style="color:#aaa; margin:0;">No description added yet.</p>
            <?php endif; ?>
        </div>

        <?php if ($isOwner): ?>
        <div id="descEdit" style="display:none;">
            <form method="POST">
                <textarea
                    id="descTextarea"
                    class="edit-area"
                    name="description"
                    maxlength="2000"
                    placeholder="Write your car build description here..."
                ><?= htmlspecialchars($post['description'] ?? "") ?></textarea>

                <div class="edit-actions">
                    <button class="save-btn" type="submit" name="update_desc">Save</button>
                    <button class="cancel-btn" type="button" onclick="toggleEditDesc(false)">Cancel</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if($user_id): ?>
    <div class="actions">
        <button id="likeBtn" class="btn <?= $user_liked?'btn-active':'' ?>" onclick="likePost(<?= $post_id ?>)">❤️ Like</button>
        <button id="voteBtn" class="btn <?= $user_voted?'btn-active':'' ?>" onclick="votePost(<?= $post_id ?>)">⭐ Vote</button>

        <textarea id="commentInput" class="comment-input" placeholder="Write a comment..."></textarea>
        <button class="btn" onclick="addComment(<?= $post_id ?>)">💬 Comment</button>
    </div>
    <?php endif; ?>

    <div class="comments">
        <h3>Comments</h3>

        <?php if ($comments && mysqli_num_rows($comments) > 0): ?>
            <?php while($c=mysqli_fetch_assoc($comments)): ?>
                <div class="comment">
                    <strong>@<?= htmlspecialchars($c['username']) ?></strong><br>
                    <small><?= htmlspecialchars($c['comment']) ?></small>

                    <?php if($user_id==$post_owner_id): ?>
                        <button class="delete-btn" onclick="delComment(<?= (int)$c['id'] ?>)">Delete</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#aaa; margin-top:10px;">No comments yet.</p>
        <?php endif; ?>
    </div>

</div>
</div>

<script>
let slides = document.querySelectorAll(".slide");
let idx = 0;

function next(){
    if (slides.length <= 1) return;
    slides[idx].classList.remove("active");
    idx = (idx+1) % slides.length;
    slides[idx].classList.add("active");
}
function prev(){
    if (slides.length <= 1) return;
    slides[idx].classList.remove("active");
    idx = (idx-1+slides.length) % slides.length;
    slides[idx].classList.add("active");
}
</script>

</body>
</html>
