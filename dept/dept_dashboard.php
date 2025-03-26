<?php
session_start();
require_once '../config/database.php';

// Check if department is logged in
if (!isset($_SESSION['dept_id'])) {
    header('Location: index.php');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

$dept_id = $_SESSION['dept_id'];
$dept_name = $_SESSION['dept_name'];

// Get unique programs for this department
$program_query = "SELECT DISTINCT program 
                 FROM students 
                 WHERE department_id = ?
                 ORDER BY program";
$prog_stmt = $conn->prepare($program_query);
$prog_stmt->bind_param("i", $dept_id);
$prog_stmt->execute();
$programs = $prog_stmt->get_result();

// Get selected program and semester
$selected_program = isset($_GET['program']) ? $_GET['program'] : null;
$selected_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : null;

// Get semesters for selected program
$semester_query = "SELECT DISTINCT current_semester 
                  FROM students 
                  WHERE department_id = ? 
                  AND program = ?
                  ORDER BY current_semester";
$sem_stmt = $conn->prepare($semester_query);
$sem_stmt->bind_param("is", $dept_id, $selected_program);
$sem_stmt->execute();
$semesters = $sem_stmt->get_result();

// Get students for selected program and semester
$students_query = "SELECT s.*, 
                  CASE 
                    WHEN s.attendance = 1 THEN 'Marked'
                    ELSE 'Pending'
                  END as attendance_status
                  FROM students s
                  WHERE s.department_id = ? 
                  AND s.program = ?
                  AND s.current_semester = ?
                  ORDER BY s.name";
$stmt = $conn->prepare($students_query);
$stmt->bind_param("isi", $dept_id, $selected_program, $selected_semester);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Dashboard - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="bg-blue-800 text-white py-4 px-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">KHANKIPUR UNIVERSITY</h1>
            <p class="text-lg"><?php echo htmlspecialchars($dept_name); ?> Department Dashboard</p>
        </div>
        <button onclick="handleLogout()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Logout
        </button>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Program and Semester Selection -->
        <div class="mb-8 flex justify-between items-center">
            <div class="flex gap-4 items-center">
                <label class="font-bold">Program:</label>
                <select id="programSelect" onchange="changeProgram(this.value)" 
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Program</option>
                    <?php 
                    while($prog = $programs->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $prog['program']; ?>" 
                            <?php echo ($selected_program == $prog['program']) ? 'selected' : ''; ?>>
                            <?php echo $prog['program']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <?php if($selected_program): ?>
                <label class="font-bold ml-4">Semester:</label>
                <select id="semesterSelect" onchange="changeSemester(this.value)" 
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    <?php echo !$selected_program ? 'disabled' : ''; ?>>
                    <option value="">Select Semester</option>
                    <?php 
                    while($sem = $semesters->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $sem['current_semester']; ?>" 
                            <?php echo ($selected_semester == $sem['current_semester']) ? 'selected' : ''; ?>>
                            Semester <?php echo $sem['current_semester']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php endif; ?>
            </div>
            <div class="flex gap-4">
                <input type="text" id="searchInput" placeholder="Search students..." 
                    class="px-4 py-2 border rounded-lg">
                <button onclick="markSelectedAttendance()" 
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Mark Selected
                </button>
            </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form id="attendanceForm">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">
                                <input type="checkbox" id="selectAll" class="mr-2">
                                Select All
                            </th>
                            <th class="px-4 py-2">Registration No.</th>
                            <th class="px-4 py-2">Roll No.</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Program</th>
                            <th class="px-4 py-2">Course</th>
                            <th class="px-4 py-2">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students->fetch_assoc()): ?>
                        <tr class="border-b student-row hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <?php if($student['attendance'] == 0): ?>
                                <input type="checkbox" name="students[]" 
                                    value="<?php echo $student['id']; ?>" 
                                    class="student-checkbox">
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($student['registration_number']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($student['roll_number']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($student['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($student['program']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($student['course']); ?></td>
                            <td class="px-4 py-2" id="status_<?php echo $student['id']; ?>"
                                class="<?php echo $student['attendance'] == 1 ? 'text-green-600' : 'text-yellow-600'; ?>">
                                <?php echo $student['attendance_status']; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Select All functionality
        $('#selectAll').change(function() {
            $('.student-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Search functionality
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".student-row").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    function changeProgram(program) {
        window.location.href = 'dept_dashboard.php?program=' + program;
    }

    function changeSemester(semester) {
        const program = $('#programSelect').val();
        window.location.href = 'dept_dashboard.php?program=' + program + '&semester=' + semester;
    }

    function markSelectedAttendance() {
        const selectedStudents = $('input[name="students[]"]:checked').map(function() {
            return this.value;
        }).get();

        if (selectedStudents.length === 0) {
            alert('Please select students to mark attendance');
            return;
        }

        if (confirm('Are you sure you want to mark attendance for selected students?')) {
            $.ajax({
                url: 'mark_attendance.php',
                method: 'POST',
                data: { student_ids: selectedStudents },
                success: function(response) {
                    const data = JSON.parse(response);
                    if(data.success) {
                        selectedStudents.forEach(id => {
                            $(`#status_${id}`).text('Marked').addClass('text-green-600');
                            $(`input[value="${id}"]`).closest('tr').find('input[type="checkbox"]').remove();
                        });
                        alert('Attendance marked successfully!');
                    } else {
                        alert(data.message || 'Error marking attendance');
                    }
                }
            });
        }
    }

    function handleLogout() {
        if(confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }
    </script>
</body>
</html> 