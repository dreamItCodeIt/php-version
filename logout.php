<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Log activity before logout
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php?msg=logged_out");
exit();
?>
