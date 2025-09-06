<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php
session_start();
include 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

if ($search !== '') {
    $like = "%$search%";
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $count_stmt->bind_param("ss", $like, $like);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id=users.id WHERE posts.title LIKE ? OR posts.content LIKE ? ORDER BY posts.created_at DESC LIMIT ?, ?");
    $stmt->bind_param("ssii", $like, $like, $offset, $limit);
} else {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts");
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id=users.id ORDER BY posts.created_at DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Blog Posts</h2>
<?php if (isset($_SESSION['username'])): ?>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>) | <a href="logout.php">Logout</a> | <a href="add.php">Add Post</a></p>
<?php else: ?>
<p><a href="login.php">Login</a> | <a href="register.php">Register</a></p>
<?php endif; ?>

<form method="GET">
  <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search posts">
  <button type="submit">Search</button>
</form>

<?php while($row = $result->fetch_assoc()): ?>
<div class="card">
  <h3><?php echo htmlspecialchars($row['title']); ?></h3>
  <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
  <small>By <?php echo htmlspecialchars($row['username']); ?> on <?php echo $row['created_at']; ?></small><br>
  <?php if (isset($_SESSION['user_id']) && ($_SESSION['role']==='admin' || $_SESSION['user_id']==$row['user_id'])): ?>
    <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
    <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this post?');">Delete</a>
  <?php endif; ?>
</div>
<?php endwhile; ?>

<?php
$totalPages = ceil($total / $limit);
for ($i=1; $i<=$totalPages; $i++) {
    echo "<a href='?page=$i&search=".urlencode($search)."'>$i</a> ";
}
?>

    </div>
</body>
</html>
