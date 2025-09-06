<?php
// db.php - safer, charset set, env-friendly
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'testdb';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // Log the detailed error for devs/admins, but show a friendly message to users
    error_log("DB Connection failed: " . $conn->connect_error);
    // In production, do not echo details
    die("Database connection error. Please try again later.");
}

if (!$conn->set_charset("utf8mb4")) {
    error_log("Failed to set DB charset: " . $conn->error);
}
