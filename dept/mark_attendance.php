<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['dept_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['student_ids']) && is_array($_POST['student_ids'])) {
        $student_ids = array_map('intval', $_POST['student_ids']);
        
        // Verify all students belong to this department
        $ids = implode(',', $student_ids);
        $verify_query = "SELECT COUNT(*) as count FROM students 
                        WHERE id IN ($ids) AND department_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("i", $_SESSION['dept_id']);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] !== count($student_ids)) {
            echo json_encode(['success' => false, 'message' => 'Invalid students selected']);
            exit();
        }
        
        // Mark attendance for all selected students
        $update_query = "UPDATE students SET attendance = 1 
                        WHERE id IN ($ids) AND department_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $_SESSION['dept_id']);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating attendance']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No students selected']);
    }
}
?> 