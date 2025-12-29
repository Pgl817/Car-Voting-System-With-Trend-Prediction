<?php
session_start();
require "config.php";

$user_id = (int)($_SESSION["user_id"] ?? 0);

$extraCols = "";
if ($user_id > 0) {
    $extraCols = ",
        IFNULL((SELECT 1 FROM likes l2 WHERE l2.post_id=c.id AND l2.user_id=$user_id LIMIT 1), 0) AS user_liked,
        IFNULL((SELECT 1 FROM votes v2 WHERE v2.post_id=c.id AND v2.user_id=$user_id LIMIT 1), 0) AS user_voted
    ";
}

$sql = "
SELECT
    c.*,
    (SELECT COUNT(*) FROM likes WHERE post_id=c.id) AS like_count,
    (SELECT COUNT(*) FROM votes WHERE post_id=c.id) AS vote_count
    $extraCols
FROM cars c
WHERE c.approval_status='Approved'
ORDER BY c.uploaded_at DESC
";
$cars = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CarVote | Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --bg:#000;
    --panel:#0f0f14;
    --line:rgba(255,255,255,.08);
    --muted:#b8b8c7;
    --muted2:#8e8ea3;
    --accent:#b35cff;
    --accent2:#5f9dff;
    --danger:#ff3d7a;
    --gold:#ffd047;
}

*{ box-sizing:border-box; }
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:var(--bg);
    color:#fff;
}

.bg-glow{
    position:fixed;
    inset:0;
    z-index:-1;
    background:
        radial-gradient(circle at 18% 22%, rgba(179,92,255,0.18), transparent 50%),
        radial-gradient(circle at 82% 72%, rgba(95,157,255,0.16), transparent 50%),
        radial-gradient(circle at 55% 30%, rgba(255,208,71,0.06), transparent 55%),
        #000;
}

.wrap{
    max-width:1200px;
    margin:0 auto;
    padding:22px 18px 40px;
}

.hero{
    text-align:center;
    padding:70px 18px 18px;
}
.title{
    font-size:44px;
    font-weight:800;
    letter-spacing:.2px;
    background:linear-gradient(90deg, var(--accent), var(--accent2));
    -webkit-background-clip:text;
    color:transparent;
    margin:0;
}
.subtitle{
    margin:14px auto 0;
    max-width:760px;
    font-size:16px;
    color:var(--muted);
    line-height:1.6;
}
.cta-row{
    margin-top:22px;
    display:flex;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
}
.btn-cta{
    padding:12px 22px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    color:#fff;
    font-weight:700;
    background:linear-gradient(90deg,#8b5cf6,#c084fc);
    box-shadow:0 0 18px rgba(179,92,255,0.45);
    transition:.22s;
}
.btn-cta:hover{
    transform:translateY(-2px) scale(1.03);
    box-shadow:0 0 28px rgba(179,92,255,0.75);
}
.welcome{
    margin-top:14px;
    color:var(--accent);
    font-weight:700;
}

.cards{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap:20px;
    max-width:1200px;
    margin:10px auto 0;
}

/* CARD */
.card{
    background:rgba(17,17,17,0.88);
    border:1px solid var(--line);
    border-radius:18px;
    overflow:hidden;
    cursor:pointer;
    transition:.22s;
    display:flex;
    flex-direction:column;
    min-height:520px;
    box-shadow:0 0 26px rgba(0,0,0,0.55);
}
.card:hover{
    transform:translateY(-6px);
    border-color: rgba(179,92,255,0.45);
    box-shadow:0 0 26px rgba(179,92,255,0.22);
}

.media{
    position:relative;
    height:240px;
    background:#000;
    overflow:hidden;
}
.media::before{
    content:"";
    position:absolute;
    inset:-30px;
    background-image: var(--bgimg);
    background-size:cover;
    background-position:center;
    filter: blur(16px);
    opacity:0.45;
    transform: scale(1.08);
}
.media::after{
    content:"";
    position:absolute;
    inset:0;
    background: linear-gradient(180deg, rgba(0,0,0,0.10), rgba(0,0,0,0.65));
}
.media img{
    position:relative;
    z-index:2;
    width:100%;
    height:100%;
    object-fit:contain;
    padding:12px;
    transition:.25s;
}
.card:hover .media img{ transform:scale(1.04); }

/* Content */
.content{
    padding:14px 14px 12px;
    display:flex;
    flex-direction:column;
    gap:10px;
    flex:1;
}
.title-row{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}
.car-name{
    margin:0;
    font-size:18px;
    font-weight:800;
    line-height:1.25;
    background:linear-gradient(90deg,#c084fc,#8b5cf6);
    -webkit-background-clip:text;
    color:transparent;
}
.by{
    margin-top:3px;
    color:var(--muted2);
    font-size:13px;
}
.badges{
    display:flex;
    gap:8px;
    align-items:center;
}
.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    color:#fff;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.10);
    white-space:nowrap;
}
.badge strong{ font-size:12px; }

.snip{
    color:#cfcfe3;
    font-size:13px;
    line-height:1.55;
    opacity:.9;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient: vertical;
    overflow:hidden;
    min-height:40px;
}

.actions{
    display:grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap:10px;
    margin-top:auto;
}
.interact-btn{
    padding:10px 10px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.10);
    background:rgba(255,255,255,0.06);
    color:#fff;
    cursor:pointer;
    transition:.2s;
    font-weight:700;
    font-size:13px;
}
.interact-btn:hover{
    transform:translateY(-1px);
    background:rgba(255,255,255,0.10);
}

.liked{
    background: rgba(255,61,122,0.18) !important;
    border-color: rgba(255,61,122,0.45) !important;
    box-shadow:0 0 14px rgba(255,61,122,0.30);
}
.voted{
    background: rgba(255,208,71,0.18) !important;
    border-color: rgba(255,208,71,0.45) !important;
    box-shadow:0 0 14px rgba(255,208,71,0.25);
}

.footer{
    padding:12px 14px 14px;
    border-top:1px solid rgba(255,255,255,0.06);
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:var(--muted2);
    font-size:12px;
}
.view{
    color:#9ec9ff;
    font-weight:700;
}
</style>
</head>

<body>
<div class="bg-glow"></div>

<?php include "header.php"; ?>

<section class="hero">
    <h1 class="title">Welcome to the Ultimate Car Community</h1>
    <p class="subtitle">
        Discover builds, vote fairly, and showcase your modified cars with a clean, modern experience.
    </p>

    <?php if (!isset($_SESSION["username"])): ?>
        <div class="cta-row">
            <button class="btn-cta" onclick="location.href='login.php'">Get Started</button>
        </div>
    <?php else: ?>
        <div class="welcome">Welcome back, <?= htmlspecialchars($_SESSION["username"]) ?>.</div>
    <?php endif; ?>
</section>

<div class="wrap">
    <div class="cards" id="cards">
        <?php while ($row = $cars->fetch_assoc()):
            $car_id = (int)$row['id'];

            $thumb = trim($row['image_path'] ?? "");

            if ($thumb === "") {
                $imgQ = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id=$car_id ORDER BY id ASC LIMIT 1");
                if ($imgQ && mysqli_num_rows($imgQ) > 0) {
                    $thumb = trim(mysqli_fetch_assoc($imgQ)['image_path'] ?? "");
                }
            }

            if ($thumb === "") $thumb = "noimage.png";

            $thumbCss = str_replace("'", "\\'", $thumb);

            $likeCount = (int)$row['like_count'];
            $voteCount = (int)$row['vote_count'];

            $userLiked = ($user_id > 0 && isset($row['user_liked']) && (int)$row['user_liked'] === 1);
            $userVoted = ($user_id > 0 && isset($row['user_voted']) && (int)$row['user_voted'] === 1);

            $desc = trim($row['description'] ?? "");
            if ($desc === "") $desc = "No description yet. Click to view the full post.";
        ?>
        <div class="card" onclick="location.href='post.php?id=<?= $car_id ?>'">
            <div class="media" style="--bgimg:url('<?= htmlspecialchars($thumbCss) ?>')">
                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($row['car_name']) ?>">
            </div>

            <div class="content">
                <div class="title-row">
                    <div>
                        <h3 class="car-name"><?= htmlspecialchars($row['car_name']) ?></h3>
                        <div class="by">By @<?= htmlspecialchars($row['username']) ?></div>
                    </div>

                    <div class="badges">
                        <div class="badge">❤️ <strong id="like<?= $car_id ?>"><?= $likeCount ?></strong></div>
                        <div class="badge">⭐ <strong id="vote<?= $car_id ?>"><?= $voteCount ?></strong></div>
                    </div>
                </div>

                <div class="snip"><?= htmlspecialchars($desc) ?></div>

                <?php if ($user_id > 0): ?>
                    <div class="actions">
                        <button
                            class="interact-btn <?= $userLiked ? 'liked' : '' ?>"
                            id="likeBtn<?= $car_id ?>"
                            onclick="event.stopPropagation(); toggleLike(<?= $car_id ?>)"
                        >❤️ Like</button>

                        <button
                            class="interact-btn <?= $userVoted ? 'voted' : '' ?>"
                            id="voteBtn<?= $car_id ?>"
                            onclick="event.stopPropagation(); toggleVote(<?= $car_id ?>)"
                        >⭐ Vote</button>

                        <button
                            class="interact-btn"
                            onclick="event.stopPropagation(); commentPost(<?= $car_id ?>)"
                        >💬 Comment</button>
                    </div>
                <?php else: ?>
                    <div class="actions" style="grid-template-columns:1fr;">
                        <button
                            class="interact-btn"
                            onclick="event.stopPropagation(); location.href='login.php'"
                        >Login to Interact</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer">
                <span>Tap card to open details</span>
                <span class="view">View Post →</span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function post(url, data) {
    return fetch(url, {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: new URLSearchParams(data)
    }).then(r => r.json());
}

function toggleLike(id) {
    post("like.php", { post_id:id }).then(res => {
        if (res.status === "not_logged_in") return alert("Please login first.");
        document.getElementById("like"+id).innerText = res.like_count;

        const btn = document.getElementById("likeBtn"+id);
        btn.classList.toggle("liked", res.action === "liked");
    });
}

function toggleVote(id) {
    post("vote.php", { post_id:id }).then(res => {
        if (res.status === "not_logged_in") return alert("Please login first.");
        if (res.status === "blocked") return alert("You cannot vote for your own car.");

        if (res.vote_count !== undefined) {
            document.getElementById("vote"+id).innerText = res.vote_count;
        }

        const btn = document.getElementById("voteBtn"+id);
        btn.classList.toggle("voted", res.action === "voted");
    });
}

function commentPost(id){
    const c = prompt("Enter your comment:");
    if(!c) return;
    post("comment.php",{ post_id:id, comment:c }).then(res=>{
        if(res.status==="not_logged_in") return alert("Please login first.");
        alert("Comment added!");
    });
}
</script>

</body>
</html>
