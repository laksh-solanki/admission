<?php
// ====================================================================
// Logout Processing Page (logout.php)
// This file clears all session attributes, destroys the session,
// and redirects the browser back to the college landing portal.
// ====================================================================

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit;
?>

