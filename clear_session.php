<?php
session_start();

// Set logout flag
$_SESSION['logging_out'] = true;

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Send response
echo json_encode(['success' => true]); 