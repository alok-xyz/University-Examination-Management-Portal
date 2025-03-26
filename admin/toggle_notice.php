<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['notice_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$notice_id = (int)$_POST['notice_id'];

$query = "UPDATE notices SET is_active = NOT is_active WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $notice_id);

echo json_encode(['success' => $stmt->execute()]); 