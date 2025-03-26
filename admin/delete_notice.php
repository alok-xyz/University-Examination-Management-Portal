<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['notice_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$notice_id = (int)$_POST['notice_id'];

// First get the file path to delete the file if exists
$query = "SELECT file_path FROM notices WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $notice_id);
$stmt->execute();
$result = $stmt->get_result();
$notice = $result->fetch_assoc();

if ($notice && $notice['file_path']) {
    $file_path = '../' . $notice['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Then delete the notice
$query = "DELETE FROM notices WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $notice_id);

echo json_encode(['success' => $stmt->execute()]); 