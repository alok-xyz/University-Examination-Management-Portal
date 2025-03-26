<?php
session_start();

header('Content-Type: application/json');

$response = ['valid' => false];

if (isset($_SESSION['student_id']) && isset($_SESSION['last_activity'])) {
    // Check if session has been inactive for more than 30 minutes
    $inactive_time = 1800; // 30 minutes in seconds
    
    if ((time() - $_SESSION['last_activity']) < $inactive_time) {
        $response['valid'] = true;
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
}

echo json_encode($response); 