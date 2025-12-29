<?php
session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($res) === 1) {
        $u = mysqli_fetch_assoc($res);

        if (password_verify($password, $u['password'])) {
            $_SESSION["user_id"] = $u["id"];
            $_SESSION["username"] = $u["username"];
            $_SESSION["role"] = $u["role"];

            header("Location: ".($u["role"]==="admin"?"admin.php":"index.php"));
            exit();
        }
        $message = "Incorrect password.";
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    margin:0;
    background:#000;
    font-family:Arial;
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh;
    color:white;
    overflow:hidden;
}

.bg-glow{
    position:fixed;
    inset:0;
    z-index:-1;
    background:
        radial-gradient(circle at 18% 22%, rgba(179,92,255,0.22), transparent 50%),
        radial-gradient(circle at 82% 72%, rgba(95,157,255,0.18), transparent 50%),
        radial-gradient(circle at 55% 30%, rgba(255,208,71,0.06), transparent 55%),
        #000;
}

.card {
    width:380px;
    background:rgba(18,18,24,0.88);
    padding:35px;
    border-radius:18px;
    box-shadow:0 0 45px rgba(0,0,0,.75);
    border:1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(12px);
}

.card h2 {
    text-align:center;
    font-size:28px;
    background:linear-gradient(90deg,#b35cff,#6f9dff);
    -webkit-background-clip:text;
    color:transparent;
    margin-bottom:6px;
}

.card p.subtitle {
    text-align:center;
    color:#aaa;
    margin-bottom:25px;
}

.input {
    width:100%;
    padding:13px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.10);
    background:rgba(255,255,255,0.06);
    color:white;
    margin-top:12px;
    font-size:14px;
    outline:none;
    transition:.2s;
}

.input:focus {
    border-color: rgba(179,92,255,0.55);
    box-shadow:0 0 18px rgba(179,92,255,0.25);
}

.btn {
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    margin-top:20px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    background:linear-gradient(90deg,#8b5cf6,#b35cff);
    color:white;
    transition:.25s;
}

.btn:hover {
    transform:translateY(-2px);
    box-shadow:0 0 18px rgba(179,92,255,.55);
}

.btn-back{
    width:100%;
    padding:13px;
    border-radius:12px;
    margin-top:12px;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.10);
    color:#fff;
    cursor:pointer;
    font-weight:bold;
    transition:.25s;
}
.btn-back:hover{
    transform:translateY(-2px);
    border-color: rgba(95,157,255,0.55);
    box-shadow:0 0 18px rgba(95,157,255,0.25);
}

.msg {
    text-align:center;
    color:#ff6b6b;
    margin-bottom:10px;
}

.footer {
    text-align:center;
    margin-top:18px;
    color:#aaa;
    font-size:14px;
}

.footer a {
    color:#b35cff;
    text-decoration:none;
}
.footer a:hover { text-decoration:underline; }
</style>
</head>

<body>
<div class="bg-glow"></div>

<div class="card">
    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to continue</p>

    <?php if($message): ?><p class="msg"><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <form method="POST">
        <input class="input" type="email" name="email" placeholder="Email" required>
        <input class="input" type="password" name="password" placeholder="Password" required>
        <button class="btn" type="submit">Login</button>
    </form>

    <button class="btn-back" type="button" onclick="location.href='index.php'">← Back to Home</button>

    <div class="footer">
        Do you have an account? <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>
