<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration = $conn->real_escape_string($_POST['registration']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $semester = (int)$conn->real_escape_string($_POST['semester']);
    
    // First check if registration exists and get current semester
    $check_reg = "SELECT id, current_semester, student_type FROM students WHERE registration_number = ?";
    $check_stmt = $conn->prepare($check_reg);
    $check_stmt->bind_param("s", $registration);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        header('Location: index.php?error=invalid_registration');
        exit();
    }

    $student_data = $check_result->fetch_assoc();
    $current_semester = (int)$student_data['current_semester'];

    // Check if semester is active first
    $semester_check = $conn->prepare("SELECT semester FROM active_semesters WHERE semester = ? AND is_active = 1");
    $semester_check->bind_param("i", $semester);
    $semester_check->execute();
    
    if ($semester_check->get_result()->num_rows === 0) {
        header('Location: index.php?error=semester_inactive');
        exit();
    }
    
    // Then validate if selected semester matches current semester
    if ($semester !== $current_semester) {
        header('Location: index.php?error=invalid_semester&current=' . $current_semester);
        exit();
    }

    // Then check password (DOB)
    $query = "SELECT *, attendance FROM students WHERE registration_number = ? AND dob = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $registration, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: index.php?error=wrong_password');
        exit();
    }
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Check attendance
        if ($student['attendance'] == 0) {
            header('Location: index.php?error=attendance_pending');
            exit();
        }

        // Set all required session variables
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['semester'] = $semester;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: index.php?error=invalid_credentials');
        exit();
    }
    
    $stmt->close();
}
?> 