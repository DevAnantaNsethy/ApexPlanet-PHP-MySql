<?php
$host = "localhost";
$user = "root";   // default XAMPP MySQL user
$pass = "";       // default is empty (no password)
$db   = "blog";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
