<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration = $conn->real_escape_string($_POST['registration']);
    $semester = (int)$conn->real_escape_string($_POST['semester']);
    
    // First check if semester is active
    $active_check = $conn->prepare("SELECT semester FROM active_semesters WHERE semester = ? AND is_active = 1");
    $active_check->bind_param("i", $semester);
    $active_check->execute();
    if ($active_check->get_result()->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected semester is not currently active for form fillup'
        ]);
        exit();
    }
    
    // Get student details with semester validation
    $query = "SELECT name, attendance, current_semester FROM students WHERE registration_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $registration);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $current_semester = (int)$student['current_semester'];
        
        // Check if selected semester matches current semester
        if ($semester !== $current_semester) {
            echo json_encode([
                'success' => false,
                'message' => 'You are not eligible for semester ' . $semester . '. Your current semester is ' . $current_semester . ' As per University Data'
            ]);
            exit();
        }
        
        // Check attendance
        if ($student['attendance'] == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Not eligible for form fill up, attendance pending! Contact with your department'
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'name' => $student['name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Not Eligible For Filling Up Examination Form! Invalid / Incorrect Registration Number'
        ]);
    }
    
    $stmt->close();
}
?> 