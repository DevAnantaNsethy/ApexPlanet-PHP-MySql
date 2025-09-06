<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
<?php
include 'db.php';
session_start();

// For dev only: remove or gate this later
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Ensure CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// simple brute-force guard (session-based)
if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
$blocked = ($_SESSION['login_attempts'] >= 8);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($blocked) {
        $error = "Too many attempts. Try again later.";
    } elseif (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            error_log("Login query failed: " . $stmt->error);
            $error = "Internal error. Please try again later.";
        } else {
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                // success
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_attempts'] = 0;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $error = "Invalid username or password.";
            }
        }
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
    <h2>Login</h2>

    <?php if(!empty($flash)) : ?>
      <p class="<?= $flash['type']==='error' ? 'flash-error' : 'flash-success' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($error)) echo "<p class='flash-error'>" . htmlspecialchars($error) . "</p>"; ?>
    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <label for="username">Username</label>
      <input id="username" type="text" name="username" required autocomplete="username" />

      <label for="password">Password</label>
      <input id="password" type="password" name="password" required autocomplete="current-password" />

      <button type="submit" <?= $blocked ? 'disabled' : '' ?>>Login</button>
    </form>
    <p>No account? <a href="register.php">Register here</a></p>
  </div>
</body>
</html>
