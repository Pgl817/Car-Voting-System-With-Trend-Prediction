<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "carvoteddb"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("❌ Database Connection Failed: " . mysqli_connect_error());
}
?>
