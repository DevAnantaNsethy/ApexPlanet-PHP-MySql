<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssii", $title, $content, $id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: index.php");
}
$post = $conn->query("SELECT * FROM posts WHERE id=$id")->fetch_assoc();
?>
<h2>Edit Post</h2>
<form method="POST">
    Title: <input type="text" name="title" value="<?php echo $post['title']; ?>"><br>
    Content: <textarea name="content"><?php echo $post['content']; ?></textarea><br>
    <button type="submit">Update</button>
</form>
