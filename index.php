<?php
session_start();
include 'db.php';

// --- Search & Pagination setup ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5; // posts per page
$offset = ($page - 1) * $limit;

// Count total matching posts
if ($search !== '') {
    $like = "%$search%";
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $count_stmt->bind_param("ss", $like, $like);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];

    // Fetch results (safe: offset/limit are ints, injected after cast)
    $query = "SELECT posts.*, users.username 
              FROM posts 
              JOIN users ON posts.user_id = users.id 
              WHERE posts.title LIKE ? OR posts.content LIKE ?
              ORDER BY posts.created_at DESC
              LIMIT $offset, $limit";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // no search
    $total = $conn->query("SELECT COUNT(*) as total FROM posts")->fetch_assoc()['total'];
    $query = "SELECT posts.*, users.username 
              FROM posts 
              JOIN users ON posts.user_id = users.id 
              ORDER BY posts.created_at DESC
              LIMIT $offset, $limit";
    $result = $conn->query($query);
}

$total_pages = max(1, ceil($total / $limit));

// Helper: build URL preserving search
function page_url($p, $search) {
    $q = [];
    if ($search !== '') $q[] = 'search=' . urlencode($search);
    $q[] = 'page=' . $p;
    return 'index.php?' . implode('&', $q);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Blog - Posts</title>
  <!-- Optional: Bootstrap CDN (safe, only front-end). Remove if offline. -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <!-- Link to your custom styles -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <a class="btn btn-sm btn-primary" href="add.php">Add Post</a>
        <a class="btn btn-sm btn-secondary" href="logout.php">Logout</a>
      </div>
      <form class="d-flex" method="get" action="index.php">
        <input class="form-control me-2" type="text" name="search" placeholder="Search title or content" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>

    <?php if ($total == 0): ?>
      <div class="alert alert-info">No posts found.</div>
    <?php else: ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            <p class="card-text"><small class="text-muted">By <?php echo htmlspecialchars($row['username']); ?> on <?php echo $row['created_at']; ?></small></p>
            <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
            <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
          </div>
        </div>
      <?php endwhile; ?>

      <!-- Pagination -->
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item <?php if($page<=1) echo 'disabled'; ?>">
            <a class="page-link" href="<?php echo ($page>1) ? page_url($page-1, $search) : '#'; ?>">Previous</a>
          </li>

          <?php
          // show at most 7 page links (simple pager)
          $start = max(1, $page - 3);
          $end = min($total_pages, $page + 3);
          if ($start > 1) {
              echo '<li class="page-item"><a class="page-link" href="'.page_url(1,$search).'">1</a></li>';
              if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
          }
          for ($p = $start; $p <= $end; $p++) {
              $active = ($p == $page) ? 'active' : '';
              echo '<li class="page-item '.$active.'"><a class="page-link" href="'.page_url($p,$search).'">'.$p.'</a></li>';
          }
          if ($end < $total_pages) {
              if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
              echo '<li class="page-item"><a class="page-link" href="'.page_url($total_pages,$search).'">'.$total_pages.'</a></li>';
          }
          ?>

          <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
            <a class="page-link" href="<?php echo ($page < $total_pages) ? page_url($page+1, $search) : '#'; ?>">Next</a>
          </li>
        </ul>
      </nav>

    <?php endif; ?>
  </div>
</body>
</html>
