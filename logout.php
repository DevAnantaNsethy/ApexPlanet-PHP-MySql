<?php
session_start();
// clear session array
$_SESSION = [];

// invalidate cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destroy
session_destroy();

// regenerate a new session id
session_start();
session_regenerate_id(true);

header("Location: login.php");
exit();
