<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!defined('CARVOTE_BG_LOADED')) {
    define('CARVOTE_BG_LOADED', true);
    ?>
    <style>
    body {
        background: #07070a;
        color: #e5e5e5;
    }

    .global-bg {
        position: fixed;
        inset: 0;
        z-index: -999;
        pointer-events: none;
        background:
            radial-gradient(circle at 15% 25%, rgba(179,92,255,0.22), transparent 45%),
            radial-gradient(circle at 85% 70%, rgba(95,157,255,0.18), transparent 48%),
            linear-gradient(180deg, #07070a 0%, #050508 100%);
    }

    .global-bg::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, transparent 55%, rgba(0,0,0,0.65));
    }
    </style>

    <div class="global-bg"></div>
    <?php
}

function shortName($name) {
    return strlen($name) > 10 ? substr($name, 0, 10) . "…" : $name;
}

$current = basename($_SERVER['PHP_SELF']);

$profilePic = "uploads/profile/default.png";
if (isset($_SESSION["user_id"])) {
    include_once "config.php";
    $uid = (int)$_SESSION["user_id"];
    $q = mysqli_query($conn, "SELECT profile_pic FROM users WHERE id=$uid");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        if (!empty($row['profile_pic'])) {
            $profilePic = $row['profile_pic'];
        }
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
.navbar {
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(12,12,18,0.85);
    padding: 16px 28px;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid #1f1f2a;
    backdrop-filter: blur(14px);
}

.nav-item {
    position: relative;
    color: #aaa;
    text-decoration: none;
    margin: 0 18px;
    padding: 10px 16px;
    border-radius: 14px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.25s ease;
}

.nav-item i {
    font-size: 18px;
}

.nav-item:hover {
    color: white;
    transform: translateY(-1px);
}

.nav-item.active {
    background: linear-gradient(90deg, #7b4bff, #b35cff);
    color: white !important;
    box-shadow: 0 6px 18px rgba(179, 92, 255, 0.45);
}

.nav-item.active::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.18);
    border-radius: 14px;
    animation: activeGlow .6s forwards;
}

@keyframes activeGlow {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.user-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 28px;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #b35cff;
    box-shadow: 0 0 10px rgba(179,92,255,0.8);
}

.username {
    color: #ddd;
    font-size: 14px;
}

.btn-login {
    background: linear-gradient(90deg, #7b4bff, #b35cff);
    padding: 9px 16px;
    border-radius: 10px;
    text-decoration: none;
    color: white;
    font-size: 14px;
    transition: 0.3s;
}
.btn-login:hover {
    opacity: 0.85;
}
</style>

<header class="navbar">

    <a href="index.php" class="nav-item <?= $current=='index.php'?'active':'' ?>">
        <i class="bi bi-house-door"></i> Home
    </a>

    <a href="discover.php" class="nav-item <?= $current=='discover.php'?'active':'' ?>">
        <i class="bi bi-compass"></i> Discover
    </a>

    <?php if (isset($_SESSION["role"]) && $_SESSION["role"]=="admin"): ?>
        <a href="admin.php" class="nav-item <?= $current=='admin.php'?'active':'' ?>">
            <i class="bi bi-clock-history"></i> Pending
        </a>
    <?php else: ?>
        <a href="upload.php" class="nav-item <?= $current=='upload.php'?'active':'' ?>">
            <i class="bi bi-cloud-upload"></i> Upload
        </a>
    <?php endif; ?>

    <a href="scoreboard.php" class="nav-item <?= $current=='scoreboard.php'?'active':'' ?>">
        <i class="bi bi-bar-chart-line"></i> Scoreboard
    </a>

    <a href="profile.php" class="nav-item <?= $current=='profile.php'?'active':'' ?>">
        <i class="bi bi-person-circle"></i> Profile
    </a>

    <?php if (isset($_SESSION["username"])): ?>
        <div class="user-box">
            <img src="<?= htmlspecialchars($profilePic) ?>" class="avatar">
            <span class="username">@<?= shortName($_SESSION["username"]) ?></span>
            <a href="logout.php" class="btn-login">Logout</a>
        </div>
    <?php else: ?>
        <a href="login.php" class="btn-login">Login</a>
    <?php endif; ?>

</header>
