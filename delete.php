<?php
// delete.php - POST-only deletion with CSRF and permission checks
session_start();
include 'db.php';

// must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // show simple forbidden / or redirect
    header("Location: index.php");
    exit();
}

if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token'];
    header("Location: index.php");
    exit();
}

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid post id'];
    header("Location: index.php");
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($isAdmin) {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
}

if ($stmt->execute()) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Post deleted.'];
} else {
    error_log("Delete failed: " . $stmt->error);
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Delete failed.'];
}

header("Location: index.php");
exit();
