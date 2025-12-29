<?php
session_start();
include "config.php";

function getThumb($conn, $post_id) {
    $post_id = (int)$post_id;

    $coverQ = mysqli_query($conn, "SELECT image_path FROM cars WHERE id=$post_id LIMIT 1");
    if ($coverQ && mysqli_num_rows($coverQ) > 0) {
        $cover = mysqli_fetch_assoc($coverQ)['image_path'] ?? "";
        $cover = trim($cover);
        if (!empty($cover)) return $cover;
    }

    $imgQ = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id=$post_id ORDER BY id ASC LIMIT 1");
    if ($imgQ && mysqli_num_rows($imgQ) > 0) {
        $p = mysqli_fetch_assoc($imgQ)['image_path'] ?? "";
        $p = trim($p);
        if (!empty($p)) return $p;
    }

    return "noimage.png";
}

function safeText($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function slot($arr, $index) {
    return isset($arr[$index]) ? $arr[$index] : null;
}

$sqlBase = "
    SELECT 
        c.id,
        c.car_name,
        c.username,
        c.uploaded_at,
        COUNT(v.id) AS vote_count
    FROM cars c
    LEFT JOIN votes v ON c.id = v.post_id
    WHERE c.approval_status = 'Approved'
    GROUP BY c.id
    ORDER BY vote_count DESC, c.uploaded_at DESC
";

$sqlTop20 = $sqlBase . " LIMIT 20";
$sqlTop3  = $sqlBase . " LIMIT 3";

$top3Res  = mysqli_query($conn, $sqlTop3);
$top20Res = mysqli_query($conn, $sqlTop20);

$top3 = [];
if ($top3Res) {
    while ($r = mysqli_fetch_assoc($top3Res)) $top3[] = $r;
}

$top3Ids = [];
foreach ($top3 as $t) $top3Ids[] = (int)$t['id'];

$first  = slot($top3, 0);
$second = slot($top3, 1);
$third  = slot($top3, 2);

$podium = [
    ["rank"=>2, "medal"=>"🥈", "data"=>$second, "colClass"=>"col-2", "pedClass"=>"ped-2"],
    ["rank"=>1, "medal"=>"🥇", "data"=>$first,  "colClass"=>"col-1", "pedClass"=>"ped-1"],
    ["rank"=>3, "medal"=>"🥉", "data"=>$third,  "colClass"=>"col-3", "pedClass"=>"ped-3"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scoreboard | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    font-family:Arial, sans-serif;
    color:#fff;
    background:#000;
}
.bg-glow{
    position:fixed;
    inset:0;
    background:
        radial-gradient(circle at 20% 20%, rgba(179,92,255,0.18), transparent 48%),
        radial-gradient(circle at 80% 70%, rgba(95,157,255,0.16), transparent 48%),
        #000;
    z-index:-1;
}
.container{
    max-width:1200px;
    margin:34px auto;
    padding:20px;
}

.title{
    text-align:center;
    margin-bottom:18px;
}
.title h1{
    margin:0;
    font-size:42px;
    background:linear-gradient(90deg,#b35cff,#5f9dff);
    -webkit-background-clip:text;
    color:transparent;
}
.title p{
    margin:10px 0 0;
    color:#bbb;
    font-size:16px;
}

.section{
    background:rgba(17,17,17,0.92);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:18px;
    box-shadow:0 0 18px rgba(0,0,0,0.55);
    padding:22px;
}
.section-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin-bottom:16px;
}
.section-head h2{
    margin:0;
    font-size:18px;
    color:#9ec9ff;
}
.section-head span{
    color:#aaa;
    font-size:13px;
}

.podium-wrap{
    overflow-x:auto;
    padding-bottom:6px;
}
.podium-grid{
    display:grid;
    grid-template-columns: repeat(3, minmax(260px, 1fr));
    gap:18px;
    align-items:end;
    min-width: 820px;
}

.podium-col{
    display:flex;
    flex-direction:column;
    gap:12px;
}

.winner{
    background:#121217;
    border:1px solid #222;
    border-radius:18px;
    padding:12px;
    cursor:pointer;
    transition:.22s;
}
.winner:hover{
    transform:translateY(-6px);
    border-color:#b35cff;
    box-shadow:0 0 16px rgba(179,92,255,0.35);
}
.winner.disabled{
    cursor:default;
    opacity:0.75;
}
.winner.disabled:hover{
    transform:none;
    border-color:#222;
    box-shadow:none;
}

.winner-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:10px;
    gap:12px;
}
.badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:6px 10px;
    border-radius:999px;
    font-weight:bold;
    font-size:13px;
    border:1px solid rgba(255,255,255,0.10);
    background:rgba(255,255,255,0.05);
}
.badge small{
    color:#bbb;
    font-weight:normal;
}
.votes{
    font-weight:bold;
    color:#9ec9ff;
    text-shadow:0 0 10px rgba(158,201,255,0.55);
}
.winner-img{
    width:100%;
    height:170px;
    border-radius:14px;
    object-fit:cover;
    background:#0c0c0c;
    border:1px solid #1f1f1f;
}
.winner-name{
    margin-top:10px;
    font-size:16px;
    font-weight:bold;
}
.winner-user{
    margin-top:4px;
    font-size:13px;
    color:#bbb;
}

.pedestal{
    border-radius:18px;
    border:1px solid rgba(255,255,255,0.08);
    display:flex;
    align-items:flex-end;
    justify-content:center;
    padding:16px 0;
    position:relative;
    overflow:hidden;
}
.pedestal::before{
    content:"";
    position:absolute;
    inset:-80px;
    background:radial-gradient(circle at 30% 30%, rgba(255,255,255,0.12), transparent 55%);
    transform:rotate(12deg);
}
.pedestal .num{
    position:relative;
    font-size:42px;
    font-weight:bold;
    opacity:0.95;
}
.ped-1{ height:160px; background:linear-gradient(180deg, rgba(255,208,71,0.28), rgba(255,208,71,0.06)); }
.ped-2{ height:125px; background:linear-gradient(180deg, rgba(158,201,255,0.22), rgba(158,201,255,0.06)); }
.ped-3{ height:110px; background:linear-gradient(180deg, rgba(255,120,200,0.18), rgba(255,120,200,0.05)); }

.col-1{ transform: translateY(-14px); }
.col-1 .winner{
    border-color: rgba(255,208,71,0.45);
    box-shadow: 0 0 18px rgba(255,208,71,0.18);
}
.col-1 .badge{ border-color: rgba(255,208,71,0.35); }

.list{ margin-top:20px; }
.item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#1a1a1f;
    padding:14px;
    margin-bottom:12px;
    border-radius:14px;
    transition:.22s;
    border:1px solid #222;
    cursor:pointer;
}
.item:hover{
    transform:translateY(-4px);
    border-color:#b35cff;
    box-shadow:0 0 14px rgba(179,92,255,0.35);
}
.left{
    display:flex;
    align-items:center;
    gap:14px;
}
.rank{
    min-width:58px;
    text-align:center;
    border-radius:12px;
    padding:8px 12px;
    font-weight:bold;
    background:linear-gradient(90deg,#5f9dff,#b35cff);
    box-shadow:0 0 12px rgba(179,92,255,0.55);
}
.thumb{
    width:82px;
    height:58px;
    border-radius:12px;
    object-fit:cover;
    background:#0c0c0c;
    border:1px solid #222;
}
.meta b{ font-size:15px; }
.meta small{ color:#bbb; }
.right{ text-align:right; }
.right b{
    font-size:22px;
    color:#9ec9ff;
    text-shadow:0 0 10px rgba(158,201,255,0.55);
}
.right span{ color:#aaa; font-size:13px; }

.empty{
    color:#bbb;
    margin:0;
    padding:10px 0 0;
}
</style>
</head>

<body>
<div class="bg-glow"></div>
<?php include "header.php"; ?>

<div class="container">

    <div class="title">
        <h1>Leaderboard</h1>
        <p>Top-voted cars across the CarVote community</p>
    </div>

    <div class="section">
        <div class="section-head">
            <h2>🏆 Ranking (Top 3)</h2>
            <span>2nd • 1st • 3rd</span>
        </div>

        <div class="podium-wrap">
            <div class="podium-grid">
                <?php foreach($podium as $p): ?>
                    <?php
                        $d = $p["data"];
                        $pid = 0;
                        $thumb = "noimage.png";
                        $car = "Not enough cars yet";
                        $user = "—";
                        $votes = 0;

                        if ($d){
                            $pid   = (int)$d["id"];
                            $thumb = getThumb($conn, $pid);
                            $car   = $d["car_name"];
                            $user  = $d["username"];
                            $votes = (int)$d["vote_count"];
                        }

                        $click = ($pid > 0) ? "onclick=\"location.href='post.php?id=$pid'\"" : "";
                        $disabledClass = ($pid > 0) ? "" : "disabled";
                    ?>

                    <div class="podium-col <?= safeText($p["colClass"]) ?>">
                        <div class="winner <?= $disabledClass ?>" <?= $click ?>>
                            <div class="winner-top">
                                <div class="badge">
                                    <?= $p["medal"] ?> #<?= (int)$p["rank"] ?>
                                    <small><?= $pid > 0 ? "Top ".$p["rank"] : "Placeholder" ?></small>
                                </div>
                                <div class="votes">⭐ <?= $votes ?></div>
                            </div>

                            <img class="winner-img" src="<?= safeText($thumb) ?>" alt="winner">
                            <div class="winner-name"><?= safeText($car) ?></div>
                            <div class="winner-user">@<?= safeText($user) ?></div>
                        </div>

                        <div class="pedestal <?= safeText($p["pedClass"]) ?>">
                            <div class="num"><?= (int)$p["rank"] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (count($top3) === 0): ?>
            <p class="empty">No cars yet. Upload a car and start voting to appear here.</p>
        <?php endif; ?>
    </div>

    <div class="section" style="margin-top:20px;">
        <div class="section-head">
            <h2>📊 Votes Ranking Board (Top 20)</h2>
            <span>Excluding Top 3</span>
        </div>

        <div class="list">
            <?php
            $rankPos = 4;
            $shown = false;

            if ($top20Res) {
                while($row = mysqli_fetch_assoc($top20Res)){
                    $pid = (int)$row["id"];
                    if (in_array($pid, $top3Ids, true)) continue;

                    $shown = true;
                    $thumb = getThumb($conn, $pid);
                    $votes = (int)$row["vote_count"];
            ?>
                <div class="item" onclick="location.href='post.php?id=<?= $pid ?>'">
                    <div class="left">
                        <div class="rank"><?= $rankPos ?></div>
                        <img class="thumb" src="<?= safeText($thumb) ?>" alt="thumb">
                        <div class="meta">
                            <b><?= safeText($row["car_name"]) ?></b><br>
                            <small>@<?= safeText($row["username"]) ?></small>
                        </div>
                    </div>
                    <div class="right">
                        <b><?= $votes ?></b><br>
                        <span>votes</span>
                    </div>
                </div>
            <?php
                    $rankPos++;
                }
            }

            if (!$shown):
            ?>
                <p class="empty">No ranked cars yet (beyond Top 3).</p>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
