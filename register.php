<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Register</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
<?php
include 'db.php';
session_start();

// For dev only: remove before prod
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $username = trim($_POST['username'] ?? '');
        $passwordPlain = trim($_POST['password'] ?? '');

        if ($username === "" || strlen($username) < 3) {
            $error = "Username must be at least 3 characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "Username must be alphanumeric (3-20 chars).";
        } elseif (strlen($passwordPlain) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // check username exists
            $check = $conn->prepare("SELECT id FROM users WHERE username=?");
            $check->bind_param("s", $username);
            if (!$check->execute()) {
                error_log("User check failed: " . $check->error);
                $error = "Internal error. Please try again later.";
            } else {
                $check->store_result();
                if ($check->num_rows > 0) {
                    $error = "Username already taken. Try another.";
                } else {
                    $password = password_hash($passwordPlain, PASSWORD_DEFAULT);
                    $role = "user";
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $password, $role);
                    if ($stmt->execute()) {
                        // Optionally auto-login:
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $conn->insert_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['flash'] = ['type'=>'success','msg'=>'Registration successful.'];
                        header("Location: index.php");
                        exit();
                    } else {
                        error_log("Register failed: " . $stmt->error);
                        $error = "Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
    <h2>Register</h2>

    <?php if(!empty($flash)) : ?>
      <p class="<?= $flash['type']==='error' ? 'flash-error' : 'flash-success' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($error)) echo "<p class='flash-error'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <label for="username">Username</label>
      <input id="username" type="text" name="username" required minlength="3" maxlength="20" />

      <label for="password">Password</label>
      <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password" />

      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
