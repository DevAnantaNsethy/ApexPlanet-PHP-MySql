<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Edit Post</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Invalid ID"); }
$id = (int)$_GET['id'];

// Fetch post
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
}

if (!$stmt->execute()) {
    error_log("Fetch post failed: " . $stmt->error);
    die("Internal error.");
}
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) { die("Post not found or no permission."); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token'];
        header("Location: edit.php?id=" . urlencode($id)); exit();
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === "" || mb_strlen($title) > 255) {
        $error = "Title invalid";
    } elseif ($content === "") {
        $error = "Content is required";
    } else {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $update = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
            $update->bind_param("ssi", $title, $content, $id);
        } else {
            $update = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=? AND user_id=?");
            $update->bind_param("ssii", $title, $content, $id, $_SESSION['user_id']);
        }

        if ($update->execute()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Post updated.'];
            header("Location: index.php"); exit();
        } else {
            error_log("Update failed: " . $update->error);
            $error = "Database error. Please try again later.";
        }
    }
}
?>

    <h2>Edit Post</h2>

    <?php if(!empty($flash)) : ?>
      <p class="<?= $flash['type']==='error' ? 'flash-error' : 'flash-success' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($error)) echo "<p class='flash-error'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <label for="title">Title</label>
      <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required maxlength="255" />

      <label for="content">Content</label>
      <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

      <button type="submit">Update</button>
    </form>
  </div>
</body>
</html>
