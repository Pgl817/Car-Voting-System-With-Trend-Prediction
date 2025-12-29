<?php
session_start();
include "config.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'")
);

if (isset($_POST['update_username'])) {
    $newname = trim($_POST['new_username']);
    mysqli_query($conn, "UPDATE users SET username='$newname' WHERE id='$user_id'");
    $_SESSION["username"] = $newname;
    $message = "✔ Username updated successfully!";
}

if (isset($_POST['update_password'])) {
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];

    if (password_verify($oldPass, $user['password'])) {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id='$user_id'");
        $message = "✔ Password changed successfully!";
    } else {
        $message = "❌ Old password is incorrect!";
    }
}

if (isset($_POST['upload_pic'])) {
    $dir = "uploads/profile/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];

    if (in_array($ext, $allowed)) {
        $path = $dir . $user_id . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $path);
        mysqli_query($conn, "UPDATE users SET profile_pic='$path' WHERE id='$user_id'");
        $message = "✔ Profile picture updated!";
    } else {
        $message = "❌ Invalid image format!";
    }
}

$mycars = mysqli_query(
    $conn,
    "SELECT * FROM cars WHERE user_id='$user_id' ORDER BY uploaded_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Profile | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    background:#000;
    color:white;
    font-family:Arial;
    margin:0;
}

.container {
    width:90%;
    max-width:1100px;
    margin:40px auto;
}

.profile-header {
    display:flex;
    align-items:center;
    gap:25px;
    background:rgba(17,17,17,0.95);
    padding:25px;
    border-radius:16px;
    border:1px solid #222;
    box-shadow:0 0 20px rgba(0,0,0,0.6);
}

.profile-header img {
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #b35cff;
    box-shadow:0 0 18px rgba(179,92,255,0.8);
}

.profile-header h2 {
    font-size:28px;
    background:linear-gradient(90deg,#b35cff,#5f9dff);
    -webkit-background-clip:text;
    color:transparent;
}

.box {
    background:#111;
    padding:22px;
    margin-top:25px;
    border-radius:14px;
    border:1px solid #222;
}

.box h3 {
    margin-top:0;
    color:#9ec9ff;
}

input, button {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:10px;
    border:none;
}

input {
    background:#222;
    color:white;
}

button {
    background:linear-gradient(90deg,#8b5cf6,#c084fc);
    color:white;
    cursor:pointer;
    transition:0.25s;
}

button:hover {
    transform:scale(1.05);
    box-shadow:0 0 14px rgba(179,92,255,0.8);
}

.msg {
    margin-top:20px;
    padding:12px;
    background:#1b1b24;
    border-radius:10px;
    text-align:center;
}

.car-grid {
    margin-top:20px;
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(260px,1fr));
    gap:20px;
}

.car-card {
    background:#111;
    border:1px solid #333;
    border-radius:14px;
    overflow:hidden;
    transition:0.25s;
}

.car-card:hover {
    transform:translateY(-6px);
    border-color:#b35cff;
    box-shadow:0 0 18px rgba(179,92,255,0.6);
}

.car-card img {
    width:100%;
    height:180px;
    object-fit:cover;
}

.car-card .info {
    padding:12px;
}

.status {
    font-size:13px;
    color:#bbb;
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<div class="container">

    <div class="profile-header">
        <img src="<?= $user['profile_pic'] ?: 'default_profile.png' ?>">
        <div>
            <h2>@<?= htmlspecialchars($user['username']) ?></h2>
            <p>User ID: <?= $user['id'] ?></p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <div class="box">
        <h3>📸 Change Profile Picture</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_pic" required>
            <button name="upload_pic">Upload</button>
        </form>
    </div>

    <div class="box">
        <h3>👤 Edit Username</h3>
        <form method="POST">
            <input type="text" name="new_username"
                   value="<?= htmlspecialchars($user['username']) ?>" required>
            <button name="update_username">Update Username</button>
        </form>
    </div>

    <div class="box">
        <h3>🔒 Change Password</h3>
        <form method="POST">
            <input type="password" name="old_password" placeholder="Old password" required>
            <input type="password" name="new_password" placeholder="New password" required>
            <button name="update_password">Update Password</button>
        </form>
    </div>

    <div class="box">
        <h3>🚗 Your Uploaded Cars</h3>

        <div class="car-grid">
            <?php if (mysqli_num_rows($mycars) == 0): ?>
                <p style="color:#bbb;">You haven’t uploaded any cars yet.</p>
            <?php endif; ?>

            <?php while ($c = mysqli_fetch_assoc($mycars)):

                $imgQ = mysqli_query($conn,
                    "SELECT image_path FROM car_images WHERE car_id={$c['id']} LIMIT 1"
                );

                if (mysqli_num_rows($imgQ) > 0) {
                    $thumb = mysqli_fetch_assoc($imgQ)['image_path'];
                } else {
                    $thumb = $c['image_path'];
                }

                if (!$thumb) $thumb = "noimage.png";
            ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($thumb) ?>">
                    <div class="info">
                        <b><?= htmlspecialchars($c['car_name']) ?></b><br>
                        <span class="status">Status: <?= $c['approval_status'] ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

</body>
</html>
