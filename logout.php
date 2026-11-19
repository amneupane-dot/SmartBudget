
<?php
session_start();

// Remove all session variables
$_SESSION = [];

// Get the session cookie settings
$params = session_get_cookie_params();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    setcookie(
        session_name(),
        "",
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Return to login page
header("Location: login.php");
exit;
