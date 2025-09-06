<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Add Post</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// Ensure CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// flash helper
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token'];
        header("Location: add.php");
        exit();
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === "" || mb_strlen($title) > 255) {
        $error = "Title is required (max 255 chars).";
    } elseif ($content === "") {
        $error = "Content is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $_SESSION['user_id']);
        if ($stmt->execute()) {
            // regenerate token after successful POST
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Post added successfully.'];
            header("Location: index.php");
            exit();
        } else {
            error_log("Add post failed: " . $stmt->error);
            $error = "Database error. Please try again later.";
        }
    }
}
?>
    <h2>Add Post</h2>

    <?php if(!empty($flash)) : ?>
      <p class="<?= $flash['type']==='error' ? 'flash-error' : 'flash-success' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($error)) echo "<p class='flash-error'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <label for="title">Title</label>
      <input id="title" type="text" name="title" required minlength="3" maxlength="255" />

      <label for="content">Content</label>
      <textarea id="content" name="content" required minlength="5"></textarea>

      <button type="submit">Add</button>
    </form>

    <script>
    document.querySelector('form').addEventListener('submit', function(e){
      var title = document.querySelector('#title').value.trim();
      var content = document.querySelector('#content').value.trim();
      if (title.length < 3) { alert('Title must be at least 3 characters'); e.preventDefault(); return; }
      if (content.length < 5) { alert('Content must be at least 5 characters'); e.preventDefault(); return; }
    });
    </script>
  </div>
</body>
</html>

