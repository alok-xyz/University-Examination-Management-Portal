<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['photo'];
$student_id = $_SESSION['student_id'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit();
}

// Validate file
if ($file['size'] > 102400) { // 100KB
    echo json_encode(['success' => false, 'message' => 'File size must be less than 100KB']);
    exit();
}

$allowed_types = ['image/jpeg', 'image/png'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG and PNG files are allowed']);
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = 'uploads/student_photos/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'student_' . $student_id . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Update database
    $query = "UPDATE students SET photo_path = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $filepath, $student_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Photo uploaded successfully',
            'photo_path' => $filepath
        ]);
    } else {
        unlink($filepath); // Delete uploaded file if database update fails
        echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
} 