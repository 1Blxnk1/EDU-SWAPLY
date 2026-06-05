<?php
require_once 'includes/functions.php';

$logout_msg = 'You have been logged out successfully';

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Start fresh session for flash message
session_start();
$_SESSION['message'] = $logout_msg;
$_SESSION['message_type'] = 'success';
redirect('index.php');
?>
