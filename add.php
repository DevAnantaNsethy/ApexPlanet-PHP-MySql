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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === "" || strlen($title) > 255) {
        $error = "Title is required (max 255 chars)";
    } elseif ($content === "") {
        $error = "Content is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $_SESSION['user_id']);
        $stmt->execute();
        header("Location: index.php");
        exit();
    }
}
?>

<h2>Add Post</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    Title: <input type="text" name="title" required minlength="3"><br>
    Content:<br>
    <textarea name="content" required minlength="5"></textarea><br>
    <button type="submit">Add</button>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e){
  var title = document.querySelector('[name=title]').value.trim();
  var content = document.querySelector('[name=content]').value.trim();
  if (title.length < 3) { alert('Title at least 3 chars'); e.preventDefault(); }
  if (content.length < 5) { alert('Content at least 5 chars'); e.preventDefault(); }
});
</script>

    </div>
</body>
</html>
