<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please login again.'
    ]);
    exit();
}

$student_id = $_SESSION['student_id'];

// Check for existing successful payment
$query = "SELECT * FROM payments WHERE student_id = ? AND status = 'success' ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    // Set payment success flag in session
    $_SESSION['payment_success'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Previous payment found'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No previous payment found. Please proceed with payment.'
    ]);
} 