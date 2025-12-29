<?php
session_start();
include "config.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "warn"; 

function isAllowedExt($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ["jpg","jpeg","png","gif","webp"]);
}

$MAX_FILE_SIZE = 5 * 1024 * 1024;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $carName     = trim($_POST["car_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $username    = $_SESSION["username"];
    $user_id     = (int)($_SESSION["user_id"] ?? 0);

    if ($carName === "" || $description === "") {
        $message = "Car name and description cannot be empty.";
        $message_type = "err";
    } else {

        $fileNames = $_FILES["car_images"]["name"] ?? [];
        $fileCount = is_array($fileNames) ? count($fileNames) : 0;

        if ($fileCount < 3 || $fileCount > 5) {
            $message = "You must upload between 3 to 5 images. (Selected: $fileCount)";
            $message_type = "err";
        } else {

            $ok = true;
            for ($i = 0; $i < $fileCount; $i++) {

                $err = $_FILES["car_images"]["error"][$i] ?? UPLOAD_ERR_NO_FILE;
                $tmp = $_FILES["car_images"]["tmp_name"][$i] ?? "";
                $name = $_FILES["car_images"]["name"][$i] ?? "";
                $size = $_FILES["car_images"]["size"][$i] ?? 0;

                if ($err !== UPLOAD_ERR_OK) {
                    $message = "One of the images failed to upload. Please try again.";
                    $message_type = "err";
                    $ok = false;
                    break;
                }

                if (!isAllowedExt($name)) {
                    $message = "Only JPG, JPEG, PNG, GIF, WEBP images are allowed.";
                    $message_type = "err";
                    $ok = false;
                    break;
                }

                if ($size > $MAX_FILE_SIZE) {
                    $message = "Each image must be 5MB or less.";
                    $message_type = "err";
                    $ok = false;
                    break;
                }

                if ($tmp && @getimagesize($tmp) === false) {
                    $message = "One of the files is not a valid image.";
                    $message_type = "err";
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                $sql = "INSERT INTO cars (user_id, username, car_name, description, approval_status)
                        VALUES (?, ?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    $message = "Database error (prepare failed).";
                    $message_type = "err";
                } else {
                    $stmt->bind_param("isss", $user_id, $username, $carName, $description);

                    if (!$stmt->execute()) {
                        $message = "Database error (insert failed).";
                        $message_type = "err";
                    } else {
                        $car_id = $stmt->insert_id;

                        $targetDir = "uploads/";
                        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                        $saved = 0;

                        for ($i = 0; $i < $fileCount; $i++) {
                            $originalName = basename($_FILES["car_images"]["name"][$i]);
                            $cleanName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", $originalName);

                            $filePath = $targetDir . time() . "_" . rand(1000, 9999) . "_" . $cleanName;

                            if (move_uploaded_file($_FILES["car_images"]["tmp_name"][$i], $filePath)) {
                                $stmtImg = $conn->prepare("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)");
                                $stmtImg->bind_param("is", $car_id, $filePath);
                                $stmtImg->execute();
                                $saved++;
                            }
                        }

                        if ($saved !== $fileCount) {
                            mysqli_query($conn, "DELETE FROM car_images WHERE car_id=$car_id");
                            mysqli_query($conn, "DELETE FROM cars WHERE id=$car_id");

                            $message = "Upload failed (some images were not saved). Please try again.";
                            $message_type = "err";
                        } else {
                            $message = "Upload successful! Waiting for admin approval.";
                            $message_type = "ok";
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Car | CarVote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --bg:#000;
    --panel: rgba(17,17,17,0.95);
    --line: rgba(255,255,255,0.08);
    --muted:#b8b8c7;
    --accent:#b35cff;
    --accent2:#5f9dff;
    --good:#1db954;
    --bad:#ff4d4d;
    --warn:#ffd047;
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
        radial-gradient(circle at 20% 25%, rgba(179,92,255,0.18), transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(95,157,255,0.16), transparent 50%),
        #000;
}

.wrap{
    max-width:1000px;
    margin:0 auto;
    padding:20px 16px 50px;
}

.upload-title{
    text-align:center;
    margin:30px 0 10px;
    font-size:40px;
    font-weight:800;
    background:linear-gradient(90deg, var(--accent), var(--accent2));
    -webkit-background-clip:text;
    color:transparent;
    animation:fadeInDown .7s ease;
}
.sub{
    text-align:center;
    color:var(--muted);
    margin:0 0 26px;
    line-height:1.6;
}

@keyframes fadeInDown{
    from{opacity:0; transform:translateY(-16px);}
    to{opacity:1; transform:translateY(0);}
}

.card{
    width: 460px;
    max-width: 100%;
    margin: 0 auto;
    background: var(--panel);
    border:1px solid var(--line);
    border-radius:18px;
    padding:22px;
    box-shadow:0 0 24px rgba(0,0,0,0.60);
}

.msg{
    padding:12px 12px;
    border-radius:12px;
    margin-bottom:14px;
    border:1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.05);
    text-align:center;
    color: var(--warn);
}

.msg.ok{ color: var(--good); border-color: rgba(29,185,84,0.25); }
.msg.err{ color: var(--bad); border-color: rgba(255,77,77,0.25); }

label{
    display:block;
    font-size:13px;
    color:#cfcfe0;
    margin:12px 0 6px;
}

input, textarea{
    width:100%;
    padding:12px;
    background: rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.10);
    border-radius:12px;
    color:#fff;
    outline:none;
    transition:.22s;
}
textarea{ resize: vertical; min-height:110px; }

input:focus, textarea:focus{
    border-color: rgba(179,92,255,0.55);
    box-shadow:0 0 18px rgba(179,92,255,0.22);
    transform: translateY(-1px);
}

.hint{
    color:#9aa0b2;
    font-size:12px;
    margin-top:6px;
    line-height:1.4;
}

button{
    width:100%;
    margin-top:16px;
    padding:12px;
    font-size:16px;
    font-weight:800;
    border:none;
    border-radius:12px;
    cursor:pointer;
    background:linear-gradient(90deg,#8b5cf6,#c084fc);
    color:#fff;
    box-shadow:0 0 16px rgba(179,92,255,0.30);
    transition:.22s;
}
button:hover{
    transform: translateY(-1px);
    box-shadow:0 0 22px rgba(179,92,255,0.50);
}
.secondary{
    background:#1f1f2a;
    box-shadow:none;
    font-weight:700;
    color:#cfd0df;
}
.secondary:hover{
    box-shadow:none;
    background:#2a2a38;
}
</style>
</head>

<body>
<div class="bg-glow"></div>
<?php include "header.php"; ?>

<div class="wrap">
    <h1 class="upload-title">Upload Your Car</h1>
    <p class="sub">You must upload <b>3–5 images</b>. Your post will be <b>Pending</b> until admin approval.</p>

    <div class="card">

        <?php if ($message): ?>
            <div class="msg <?= $message_type === 'ok' ? 'ok' : ($message_type === 'err' ? 'err' : '') ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <script>
                alert("<?= addslashes($message) ?>");
            </script>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="uploadForm">

            <label>Car Name</label>
            <input type="text" name="car_name" required maxlength="80" placeholder="e.g. Civic FD2R">

            <label>Description</label>
            <textarea name="description" required maxlength="1500" placeholder="Write what mods you did, engine, wheels, etc..."></textarea>

            <label>Upload Images (3–5)</label>
            <input type="file" id="carImages" name="car_images[]" multiple accept="image/*" required>
            <div class="hint">
                Tip: Upload clear front/side/interior shots. (Max 5 images, each ≤ 5MB)
            </div>

            <button type="submit">Upload Now</button>
            <button type="button" class="secondary" onclick="location.href='index.php'">Cancel / Back</button>
        </form>

    </div>
</div>

<script>
const input = document.getElementById("carImages");
const form  = document.getElementById("uploadForm");

function validateCount(showAlert=true){
    const files = input.files;
    const count = files ? files.length : 0;

    if(count < 3 || count > 5){
        if(showAlert){
            alert(`You must upload between 3 to 5 images. (Selected: ${count})`);
        }
        return false;
    }
    return true;
}

input.addEventListener("change", () => {
    validateCount(true);
});

form.addEventListener("submit", (e) => {
    if(!validateCount(true)){
        e.preventDefault();
    }
});
</script>

</body>
</html>
