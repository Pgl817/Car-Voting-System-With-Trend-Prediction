<?php
session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // NEW: password min length 6
    if (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (username,email,password,role) VALUES (?,?,?,'user')"
            );
            $stmt->bind_param("sss", $username, $email, $hash);
            $stmt->execute();

            echo "<script>
                setTimeout(()=>location.href='login.php',1200);
            </script>";
            $message = "Account created successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    margin:0;
    font-family:Arial;
    background:#000;
    color:white;
}
.bg-glow {
    position:fixed;
    inset:0;
    background:
        radial-gradient(circle at 20% 30%, rgba(179,92,255,0.18), transparent 45%),
        radial-gradient(circle at 80% 70%, rgba(95,157,255,0.15), transparent 45%),
        #000;
    z-index:-1;
}
.page {
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:30px;
}
.card {
    width:420px;
    background:#111;
    border-radius:14px;
    padding:28px;
    border:1px solid #222;
    box-shadow:
        0 0 25px rgba(0,0,0,.6),
        0 0 35px rgba(179,92,255,.15);
}
.card h2 {
    font-size:26px;
    text-align:center;
    background:linear-gradient(90deg,#b35cff,#5f9dff);
    -webkit-background-clip:text;
    color:transparent;
    margin-bottom:6px;
}
.card p.sub {
    text-align:center;
    color:#aaa;
    margin-bottom:22px;
    font-size:14px;
}
label {
    font-size:13px;
    color:#bbb;
    display:block;
    margin-top:12px;
}
input {
    width:100%;
    padding:11px;
    border-radius:8px;
    border:none;
    background:#1a1a1a;
    color:white;
    margin-top:6px;
}
input:focus {
    outline:none;
    box-shadow:0 0 0 1px #b35cff;
}
.btn-main {
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    margin-top:20px;
    font-weight:bold;
    cursor:pointer;
    background:linear-gradient(90deg,#8b5cf6,#b35cff);
    color:white;
    transition:.25s;
    box-shadow:0 0 18px rgba(179,92,255,.4);
}
.btn-main:hover {
    transform:scale(1.04);
    box-shadow:0 0 26px rgba(179,92,255,.7);
}
.btn-secondary {
    width:100%;
    padding:11px;
    margin-top:10px;
    background:#222;
    border:none;
    border-radius:10px;
    color:#ccc;
    cursor:pointer;
}
.btn-secondary:hover {
    background:#2d2d2d;
}
.msg {
    background:#1f1f1f;
    padding:10px;
    border-radius:8px;
    text-align:center;
    color:#ffd24d;
    margin-bottom:12px;
    font-size:14px;
}
.footer {
    text-align:center;
    margin-top:16px;
    font-size:13px;
    color:#aaa;
}
.footer a {
    color:#b35cff;
    text-decoration:none;
}
.footer a:hover {
    text-decoration:underline;
}
</style>
</head>

<body>

<div class="bg-glow"></div>

<div class="page">
    <div class="card">

        <h2>Create Account</h2>
        <p class="sub">Join the CarVote community</p>

        <?php if ($message): ?>
            <div class="msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" minlength="6" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm" minlength="6" required>

            <button class="btn-main">Create Account</button>
        </form>

        <button class="btn-secondary" onclick="location.href='index.php'">
            Cancel / Back to Home
        </button>

        <div class="footer">
            Already have an account?
            <a href="login.php">Sign in</a>
        </div>

    </div>
</div>

</body>
</html>
