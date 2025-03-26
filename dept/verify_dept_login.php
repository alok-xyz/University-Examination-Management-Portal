<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = $conn->real_escape_string($_POST['department']);
    $login_id = $conn->real_escape_string($_POST['login_id']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $query = "SELECT id, name FROM departments 
              WHERE id = ? AND login_id = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $department_id, $login_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $department = $result->fetch_assoc();
        $_SESSION['dept_id'] = $department['id'];
        $_SESSION['dept_name'] = $department['name'];
        $_SESSION['last_activity'] = time();
        
        header('Location: dept_dashboard.php');
        exit();
    } else {
        header('Location: index.php?error=invalid_credentials');
        exit();
    }
}
?> 