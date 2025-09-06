<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="conatiner">
        <?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Invalid ID"); }
$id = (int)$_GET['id'];

// Fetch post
if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
}
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) { die("Post not found or no permission."); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === "" || strlen($title) > 255) {
        $error = "Title invalid";
    } elseif ($content === "") {
        $error = "Content is required";
    } else {
        if ($_SESSION['role'] === 'admin') {
            $update = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
            $update->bind_param("ssi", $title, $content, $id);
        } else {
            $update = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=? AND user_id=?");
            $update->bind_param("ssii", $title, $content, $id, $_SESSION['user_id']);
        }
        $update->execute();
        header("Location: index.php");
        exit();
    }
}
?>

<h2>Edit Post</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    Title: <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br>
    Content:<br>
    <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea><br>
    <button type="submit">Update</button>
</form>

    </div>
</body>
</html>
