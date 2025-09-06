<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: index.php"); exit(); }
$id = (int) $_GET['id'];

if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
}
$stmt->execute();
header("Location: index.php");
exit();
?>
