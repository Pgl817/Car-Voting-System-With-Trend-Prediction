<?php
session_start();
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}


if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION["csrf_token"];


$allowedStatus = ["Pending", "Approved", "Rejected", "All"];
$status = $_GET["status"] ?? "Pending";
if (!in_array($status, $allowedStatus, true)) $status = "Pending";


$where = ($status === "All") ? "" : "WHERE approval_status='$status'";


function countStatus($conn, $s) {
    if ($s === "All") {
        $q = $conn->query("SELECT COUNT(*) AS c FROM cars");
    } else {
        $q = $conn->query("SELECT COUNT(*) AS c FROM cars WHERE approval_status='$s'");
    }
    $r = $q ? $q->fetch_assoc() : ["c" => 0];
    return (int)($r["c"] ?? 0);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token    = $_POST["csrf"] ?? "";
    $action   = $_POST["action"] ?? "";
    $id       = (int)($_POST["id"] ?? 0);
    $cover    = trim($_POST["cover_path"] ?? "");

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        header("Location: admin.php?status=" . urlencode($status) . "&msg=" . urlencode("Invalid request."));
        exit();
    }

    if ($id <= 0) {
        header("Location: admin.php?status=" . urlencode($status) . "&msg=" . urlencode("Invalid post id."));
        exit();
    }

    
    $saveCoverIfValid = function() use ($conn, $id, $cover) {
        if ($cover === "") return true; 

        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM car_images WHERE car_id=? AND image_path=?");
        $stmt->bind_param("is", $id, $cover);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ((int)($res["c"] ?? 0) <= 0) return false;

        $up = $conn->prepare("UPDATE cars SET image_path=? WHERE id=?");
        $up->bind_param("si", $cover, $id);
        $ok = $up->execute();
        $up->close();

        return $ok;
    };

    if ($action === "set_cover") {
        $ok = $saveCoverIfValid();
        $msg = $ok ? "Cover updated for post #$id." : "Cover update failed (invalid image).";
        header("Location: admin.php?status=" . urlencode($status) . "&msg=" . urlencode($msg));
        exit();
    }

    if ($action === "approve") {
        
        if (!$saveCoverIfValid()) {
            header("Location: admin.php?status=" . urlencode($status) . "&msg=" . urlencode("Approve failed: invalid cover image."));
            exit();
        }

        $stmt = $conn->prepare("UPDATE cars SET approval_status='Approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php?status=Approved&msg=" . urlencode("Post #$id approved (moved to Approved)."));
        exit();
    }

    if ($action === "reject") {
        $stmt = $conn->prepare("UPDATE cars SET approval_status='Rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php?status=Rejected&msg=" . urlencode("Post #$id rejected (moved to Rejected)."));
        exit();
    }

    if ($action === "delete") {
        
        $imgs = $conn->prepare("SELECT image_path FROM car_images WHERE car_id=?");
        $imgs->bind_param("i", $id);
        $imgs->execute();
        $res = $imgs->get_result();
        while ($i = $res->fetch_assoc()) {
            $path = $i["image_path"] ?? "";
            if ($path && file_exists($path)) @unlink($path);
        }
        $imgs->close();

        
        $conn->query("DELETE FROM car_images WHERE car_id=$id");
        $conn->query("DELETE FROM comments WHERE post_id=$id");
        $conn->query("DELETE FROM likes WHERE post_id=$id");
        $conn->query("DELETE FROM votes WHERE post_id=$id");
        $conn->query("DELETE FROM cars WHERE id=$id");

        header("Location: admin.php?status=" . urlencode($status) . "&msg=" . urlencode("Post #$id deleted."));
        exit();
    }

    header("Location: admin.php?status=" . urlencode($status));
    exit();
}


$countPending  = countStatus($conn, "Pending");
$countApproved = countStatus($conn, "Approved");
$countRejected = countStatus($conn, "Rejected");
$countAll      = countStatus($conn, "All");


$msg = $_GET["msg"] ?? "";
$result = $conn->query("SELECT * FROM cars $where ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Moderation | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --bg:#000;
    --panel:rgba(17,17,17,0.90);
    --line:rgba(255,255,255,0.08);
    --muted:#a9a9b8;
    --accent:#b35cff;
    --accent2:#6f9dff;
    --warn:#ffd047;
}
*{ box-sizing:border-box; }
body{
    margin:0;
    font-family:Arial, sans-serif;
    color:#eaeaea;
    background:var(--bg);
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
    max-width:1250px;
    margin:24px auto;
    padding:18px;
}


.topbar{
    position:relative;
    padding:18px 18px 16px;
    border-radius:18px;
    background:var(--panel);
    border:1px solid var(--line);
    box-shadow:0 0 18px rgba(0,0,0,0.55);
    backdrop-filter: blur(10px);
}
.topbar h1{
    margin:0;
    font-size:28px;
    text-align:center;
    background:linear-gradient(90deg,var(--accent),var(--accent2));
    -webkit-background-clip:text;
    color:transparent;
    font-weight:800;
    letter-spacing:.2px;
}
.topbar .sub{
    margin-top:10px;
    text-align:center;
    color:var(--muted);
    font-size:13px;
    line-height:1.5;
}
.logout-btn{
    position:absolute;
    right:16px;
    top:16px;
    text-decoration:none;
    color:#fff;
    background:#121217;
    border:1px solid #222;
    padding:10px 14px;
    border-radius:999px;
    font-size:13px;
    transition:.2s;
}
.logout-btn:hover{
    border-color:var(--accent);
    box-shadow:0 0 14px rgba(179,92,255,.25);
}


.tabs{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    justify-content:center;
    margin:14px 0 14px;
}
.tab{
    text-decoration:none;
    color:#bbb;
    background:#121217;
    border:1px solid #222;
    padding:10px 14px;
    border-radius:999px;
    transition:.22s;
    font-size:13px;
    font-weight:700;
}
.tab:hover{
    border-color:var(--accent);
    box-shadow:0 0 14px rgba(179,92,255,.20);
    color:#fff;
}
.tab.active{
    background:linear-gradient(90deg,#7b4bff,var(--accent));
    color:#fff;
    border-color:transparent;
    box-shadow:0 0 16px rgba(179,92,255,.32);
    transform: translateY(-1px);
}
.tab b{ color:#fff; }


.toast{
    margin:10px auto 0;
    max-width:1250px;
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    padding:12px 14px;
    color:var(--warn);
}

.table-card{
    margin-top:12px;
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 0 18px rgba(0,0,0,0.55);
}
.table-wrap{ overflow-x:auto; }
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    min-width:1120px;
}
th{
    position:sticky;
    top:0;
    z-index:2;
    background:rgba(20,20,28,0.92);
    backdrop-filter: blur(10px);
    color:#aaa;
    font-size:13px;
    padding:14px;
    text-align:left;
    border-bottom:1px solid #1f1f2a;
}
td{
    padding:14px;
    border-bottom:1px solid #1f1f2a;
    font-size:14px;
    vertical-align:middle;
}
tbody tr:nth-child(even) td{ background: rgba(255,255,255,0.015); }
tr:hover td{ background:#12121a; }

.thumb{
    width:128px;
    height:78px;
    border-radius:12px;
    object-fit:cover;
    background:#0c0c0c;
    border:1px solid #222;
    cursor:pointer;
    transition:.2s;
    box-shadow: 0 10px 25px rgba(0,0,0,0.35);
}
.thumb:hover{
    transform:scale(1.04);
    border-color:var(--accent);
    box-shadow:0 14px 34px rgba(0,0,0,0.55);
}
.small{
    margin-top:8px;
    font-size:12px;
    color:#9a9aac;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,0.10);
    background:rgba(255,255,255,0.05);
    font-size:12px;
    color:#ddd;
}
.badge.pending{ border-color: rgba(255,208,71,0.35); }
.badge.approved{ border-color: rgba(29,185,84,0.35); }
.badge.rejected{ border-color: rgba(230,57,70,0.35); }
.dot{ width:8px; height:8px; border-radius:999px; }
.dot.pending{ background:#ffd047; }
.dot.approved{ background:#1db954; }
.dot.rejected{ background:#e63946; }

.actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.btn{
    border:none;
    cursor:pointer;
    padding:8px 12px;
    border-radius:10px;
    color:#fff;
    font-size:12px;
    transition:.2s;
    background:#1f1f2a;
    border:1px solid #2a2a38;
}
.btn:hover{
    transform:translateY(-2px);
    border-color:var(--accent);
    box-shadow:0 0 14px rgba(179,92,255,.18);
}
.btn-approve{ background:rgba(29,185,84,0.15); border-color:rgba(29,185,84,0.25); }
.btn-reject{ background:rgba(230,57,70,0.15); border-color:rgba(230,57,70,0.25); }
.btn-delete{ background:rgba(139,0,0,0.22); border-color:rgba(139,0,0,0.25); }
.btn-cover{  background:rgba(95,157,255,0.14); border-color:rgba(95,157,255,0.25); }

.empty{
    text-align:center;
    padding:26px 18px;
    color:#bbb;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.92);
    z-index:1000;
    justify-content:center;
    align-items:center;
    padding:18px;
}
.modal-box{
    width:100%;
    max-width:980px;
    background:#14141c;
    border-radius:18px;
    padding:18px;
    position:relative;
    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 0 40px rgba(0,0,0,.8);
}
.modal-close{
    position:absolute;
    top:14px;
    right:16px;
    font-size:22px;
    cursor:pointer;
    color:#aaa;
}
.modal-close:hover{ color:#fff; }
.modal-title{
    text-align:center;
    margin:4px 0 12px;
    font-weight:800;
    color:#cfd2ff;
    letter-spacing:.2px;
}
.modal-main{
    width:100%;
    max-height:480px;
    object-fit:contain;
    border-radius:14px;
    background:#000;
    border:1px solid #1f1f2a;
}
.thumb-row{
    display:flex;
    gap:10px;
    justify-content:center;
    flex-wrap:wrap;
    margin-top:14px;
}
.thumb-row img{
    width:120px;
    height:80px;
    object-fit:cover;
    border-radius:12px;
    cursor:pointer;
    opacity:.70;
    border:1px solid #222;
    transition:.18s;
}
.thumb-row img:hover{
    opacity:1;
    border-color:var(--accent);
}
.thumb-row img.selected{
    opacity:1;
    border-color:var(--accent2);
    box-shadow:0 0 16px rgba(95,157,255,.25);
}
.modal-hint{
    margin-top:10px;
    text-align:center;
    font-size:13px;
    color:#a9a9b8;
}
.modal-actions{
    display:flex;
    gap:10px;
    justify-content:center;
    flex-wrap:wrap;
    margin-top:14px;
}
.modal-actions form{ margin:0; }
.modal-actions button{
    padding:10px 18px;
    border-radius:999px;
    font-size:14px;
}
</style>
</head>

<body>
<div class="bg-glow"></div>

<div class="container">

    <div class="topbar">
        <a class="logout-btn" href="logout.php">Logout</a>
        <h1>Admin Moderation</h1>
        <div class="sub">
            Click the preview to open gallery. Click a thumbnail to choose cover, then Save Cover or Approve.
        </div>
    </div>

    <div class="tabs">
        <a class="tab <?= $status==="Pending" ? "active" : "" ?>"  href="admin.php?status=Pending">Pending <b>(<?= $countPending ?>)</b></a>
        <a class="tab <?= $status==="Approved" ? "active" : "" ?>" href="admin.php?status=Approved">Approved <b>(<?= $countApproved ?>)</b></a>
        <a class="tab <?= $status==="Rejected" ? "active" : "" ?>" href="admin.php?status=Rejected">Rejected <b>(<?= $countRejected ?>)</b></a>
        <a class="tab <?= $status==="All" ? "active" : "" ?>"      href="admin.php?status=All">All <b>(<?= $countAll ?>)</b></a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="toast"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:90px;">ID</th>
                        <th style="width:200px;">User</th>
                        <th>Car</th>
                        <th style="width:220px;">Preview (Cover)</th>
                        <th style="width:170px;">Status</th>
                        <th style="width:360px;">Actions</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (!$result || $result->num_rows === 0): ?>
                    <tr><td colspan="6" class="empty">No posts found for this filter.</td></tr>
                <?php else: ?>

                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                            $pid = (int)$row["id"];

                            $imgs = [];
                            $iq = $conn->query("SELECT image_path FROM car_images WHERE car_id=$pid ORDER BY id ASC");
                            if ($iq) {
                                while($i = $iq->fetch_assoc()) {
                                    if (!empty($i["image_path"])) $imgs[] = $i["image_path"];
                                }
                            }

                            $cover = trim($row["image_path"] ?? "");
                            if ($cover === "" && count($imgs) > 0) $cover = $imgs[0];
                            if ($cover === "") $cover = "noimage.png";

                            $imgCount = count($imgs);

                            $statusText = $row["approval_status"] ?? "Pending";
                            $badgeClass = "pending";
                            if ($statusText === "Approved") $badgeClass = "approved";
                            if ($statusText === "Rejected") $badgeClass = "rejected";
                        ?>

                        <tr>
                            <td>#<?= $pid ?></td>
                            <td>@<?= htmlspecialchars($row["username"] ?? "") ?></td>
                            <td><?= htmlspecialchars($row["car_name"] ?? "") ?></td>

                            <td>
                                <img class="thumb"
                                     src="<?= htmlspecialchars($cover) ?>"
                                     data-status="<?= htmlspecialchars($statusText) ?>"
                                     data-cover="<?= htmlspecialchars($row["image_path"] ?? "") ?>"
                                     onclick="openGallery(<?= $pid ?>, this.getAttribute('data-status'), this.getAttribute('data-cover'))"
                                     alt="cover">

                                <div class="small"><?= $imgCount ?> images</div>

                                <script type="application/json" id="imgs-<?= $pid ?>">
                                    <?= json_encode($imgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
                                </script>
                            </td>

                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <span class="dot <?= $badgeClass ?>"></span>
                                    <?= htmlspecialchars($statusText) ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <?php if ($statusText === "Pending"): ?>
                                        <button class="btn btn-cover" type="button"
                                            onclick="openGallery(<?= $pid ?>, 'Pending', '<?= htmlspecialchars($row['image_path'] ?? "") ?>')">
                                            Choose Cover
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-cover" type="button"
                                            onclick="openGallery(<?= $pid ?>, '<?= htmlspecialchars($statusText) ?>', '<?= htmlspecialchars($row['image_path'] ?? "") ?>')">
                                            Change Cover
                                        </button>
                                    <?php endif; ?>

                                    <form method="POST">
                                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $pid ?>">
                                        <button class="btn btn-delete" type="submit"
                                            onclick="return confirm('Delete post #<?= $pid ?> permanently?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL -->
<div class="modal" id="modal" onclick="closeModal(event)">
    <div class="modal-box">
        <span class="modal-close" onclick="closeModal()">✖</span>
        <div class="modal-title" id="modalTitle">Preview</div>
        <img class="modal-main" id="mainImg" alt="preview">
        <div class="thumb-row" id="thumbRow"></div>
        <div class="modal-hint" id="modalHint">Click a thumbnail to select cover.</div>
        <div class="modal-actions" id="modalActions"></div>
    </div>
</div>

<script>
const CSRF = "<?= $csrf ?>";

let selectedCover = ""; 
let currentPostId = 0;
let currentPostStatus = "Pending";

function openGallery(id, postStatus, currentCover){
    currentPostId = id;
    currentPostStatus = postStatus || "Pending";

    const modal = document.getElementById("modal");
    const main  = document.getElementById("mainImg");
    const row   = document.getElementById("thumbRow");
    const act   = document.getElementById("modalActions");
    const ttl   = document.getElementById("modalTitle");
    const hint  = document.getElementById("modalHint");

    row.innerHTML = "";
    act.innerHTML = "";

    
    let imgs = [];
    try{
        const jsonTag = document.getElementById("imgs-" + id);
        imgs = JSON.parse(jsonTag.textContent || "[]");
    }catch(e){ imgs = []; }

    modal.style.display = "flex";

    ttl.textContent = `Post #${id} — ${currentPostStatus}`;
    hint.textContent = imgs.length ? "Click a thumbnail to select cover (then Save Cover / Approve)." : "No images for this post.";

    selectedCover = "";
    if (currentCover && currentCover.trim() !== "") {
        selectedCover = currentCover.trim();
    } else if (imgs.length) {
        selectedCover = imgs[0];
    }

    if (!imgs.length){
        main.src = "noimage.png";
    } else {
        main.src = selectedCover || imgs[0];

        imgs.forEach(src => {
            const t = document.createElement("img");
            t.src = src;

            if (selectedCover && src === selectedCover) t.classList.add("selected");

            t.onclick = () => {
                selectedCover = src;
                main.src = src;
                [...row.querySelectorAll("img")].forEach(x => x.classList.remove("selected"));
                t.classList.add("selected");
            };

            row.appendChild(t);
        });
    }

    let buttons = `
        <form method="POST">
            <input type="hidden" name="csrf" value="${CSRF}">
            <input type="hidden" name="action" value="set_cover">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="cover_path" value="${escapeHtml(selectedCover)}" id="coverInputSave">
            <button class="btn btn-cover" type="submit"
                onclick="document.getElementById('coverInputSave').value = selectedCover; return confirm('Save this cover for post #${id}?')">
                Save Cover
            </button>
        </form>
    `;

    if (currentPostStatus === "Pending") {
        buttons += `
            <form method="POST">
                <input type="hidden" name="csrf" value="${CSRF}">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="cover_path" value="${escapeHtml(selectedCover)}" id="coverInputApprove">
                <button class="btn btn-approve" type="submit"
                    onclick="document.getElementById('coverInputApprove').value = selectedCover; return confirm('Approve post #${id} with selected cover?')">
                    Approve
                </button>
            </form>

            <form method="POST">
                <input type="hidden" name="csrf" value="${CSRF}">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="${id}">
                <button class="btn btn-reject" type="submit"
                    onclick="return confirm('Reject post #${id}?')">
                    Reject
                </button>
            </form>
        `;
    }

    buttons += `
        <form method="POST">
            <input type="hidden" name="csrf" value="${CSRF}">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
            <button class="btn btn-delete" type="submit"
                onclick="return confirm('Delete post #${id} permanently?')">
                Delete
            </button>
        </form>
    `;

    act.innerHTML = buttons;
}

function closeModal(e){
    if(!e || e.target.id === "modal"){
        document.getElementById("modal").style.display = "none";
    }
}

document.addEventListener("keydown", (e)=>{
    if (e.key === "Escape") closeModal();
});

function escapeHtml(str){
    return (str || "")
        .replaceAll("&","&amp;")
        .replaceAll("\"","&quot;")
        .replaceAll("'","&#039;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;");
}
</script>

</body>
</html>
