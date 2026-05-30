<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Start temporary session just to set a flash message
session_start();
$_SESSION['flash_message'] = "You have been logged out successfully.";
$_SESSION['flash_type'] = "info";

header("Location: login.php");
exit;
?>
