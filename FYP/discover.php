<?php
session_start();
include "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Discover Cars | CarVote</title>
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
    padding:26px 18px 46px;
}


.discover-head{
    text-align:center;
    padding:24px 10px 6px;
    animation: fadeInDown .7s ease;
}
.discover-head h1{
    margin:0;
    font-size:42px;
    font-weight:800;
    background:linear-gradient(90deg, var(--accent), var(--accent2));
    -webkit-background-clip:text;
    color:transparent;
}
.discover-head p{
    margin:12px auto 0;
    max-width:760px;
    color:var(--muted);
    line-height:1.6;
    font-size:16px;
}
@keyframes fadeInDown{
    from{ opacity:0; transform:translateY(-18px); }
    to{ opacity:1; transform:translateY(0); }
}


.search-wrap{
    margin:18px auto 24px;
    max-width:680px;
    display:flex;
    justify-content:center;
}
.search{
    width:100%;
    padding:14px 18px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,0.10);
    outline:none;
    background:rgba(255,255,255,0.06);
    color:#fff;
    font-size:15px;
    box-shadow:0 0 18px rgba(179,92,255,0.16);
    transition:.22s;
}
.search:focus{
    border-color: rgba(179,92,255,0.45);
    box-shadow:0 0 26px rgba(179,92,255,0.30);
    transform: translateY(-1px);
}
.search::placeholder{ color:#bdbdd0; }


.grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap:20px;
}


.card{
    background:rgba(17,17,17,0.88);
    border:1px solid var(--line);
    border-radius:18px;
    overflow:hidden;
    cursor:pointer;
    transition:.22s;
    box-shadow:0 0 26px rgba(0,0,0,0.55);
    display:flex;
    flex-direction:column;
    min-height:380px; 
}
.card:hover{
    transform:translateY(-6px);
    border-color: rgba(179,92,255,0.45);
    box-shadow:0 0 26px rgba(179,92,255,0.22);
}


.media{
    position:relative;
    height:220px;
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


.info{
    padding:14px 14px 16px;
    display:flex;
    flex-direction:column;
    gap:8px;
    flex:1;
}
.info h3{
    margin:0;
    font-size:18px;
    font-weight:800;
    background:linear-gradient(90deg,#c084fc,#8b5cf6);
    -webkit-background-clip:text;
    color:transparent;
    line-height:1.25;
}
.info .by{
    color:var(--muted2);
    font-size:13px;
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

<script>
function filterCars(){
    const input = document.getElementById("searchBox").value.toLowerCase();
    const cards = document.getElementsByClassName("card");

    for(let i=0;i<cards.length;i++){
        const titleEl = cards[i].querySelector("h3");
        const userEl  = cards[i].querySelector(".by");
        const title = titleEl ? titleEl.innerText.toLowerCase() : "";
        const user  = userEl ? userEl.innerText.toLowerCase() : "";

        // allow searching car name OR username
        const ok = title.includes(input) || user.includes(input);
        cards[i].style.display = ok ? "flex" : "none";
    }
}
</script>
</head>

<body>
<div class="bg-glow"></div>

<?php include "header.php"; ?>

<div class="wrap">

    <div class="discover-head">
        <h1>Discover</h1>
        <p>Explore approved builds from the CarVote community. Search by car name or username.</p>
    </div>

    <div class="search-wrap">
        <input type="text" id="searchBox" class="search"
               onkeyup="filterCars()" placeholder="Search car name or @username...">
    </div>

    <div class="grid">
    <?php
    $sql = "SELECT * FROM cars WHERE approval_status='Approved' ORDER BY uploaded_at DESC";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)):

        $post_id = (int)$row['id'];

        $thumb = trim($row['image_path'] ?? "");

        if ($thumb === "") {
           $imgQuery = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id=$post_id ORDER BY id ASC LIMIT 1");
           if ($imgQuery && mysqli_num_rows($imgQuery) > 0) {
              $thumb = mysqli_fetch_assoc($imgQuery)['image_path'] ?? "";
           }
        }

if ($thumb === "") $thumb = "noimage.png";

    ?>
        <div class="card" onclick="location.href='post.php?id=<?= $post_id ?>'">
            <div class="media" style="--bgimg:url('<?= htmlspecialchars($thumb) ?>')">
                <img src="<?= htmlspecialchars($thumb) ?>" alt="Car image">
            </div>

            <div class="info">
                <h3><?= htmlspecialchars($row['car_name']) ?></h3>
                <div class="by">@<?= htmlspecialchars($row['username']) ?></div>
            </div>

            <div class="footer">
                <span>Tap to open</span>
                <span class="view">View Post →</span>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

</div>
</body>
</html>
