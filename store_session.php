<?php
session_start();

if (!isset($_SESSION['student_id']) || !isset($_POST['action'])) {
    http_response_code(400);
    exit('Invalid request');
}

switch ($_POST['action']) {
    case 'payment_initiated':
        $_SESSION['payment_initiated'] = true;
        $_SESSION['payment_time'] = time();
        
        // Store selected papers in session
        if(isset($_POST['selected_papers'])) {
            $_SESSION['selected_papers'] = $_POST['selected_papers'];
        }
        break;
    
    default:
        http_response_code(400);
        exit('Invalid action');
}

echo json_encode(['success' => true]); 