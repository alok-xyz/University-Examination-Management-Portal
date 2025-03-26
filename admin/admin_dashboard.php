<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get filter parameters
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : null;
$payment_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : null;
$department = isset($_GET['department']) ? (int)$_GET['department'] : null;

// Fetch notices for management
$notices_query = "SELECT * FROM notices ORDER BY created_at DESC LIMIT 10";
$notices = $conn->query($notices_query);

// Build the payment query
$query = "SELECT s.*, d.name as department_name,
          CASE WHEN p.id IS NOT NULL THEN 'Paid' ELSE 'Pending' END as payment_status,
          p.created_at as payment_date, p.payment_id as transaction_id, p.amount
          FROM students s
          LEFT JOIN departments d ON s.department_id = d.id
          LEFT JOIN payments p ON s.id = p.student_id AND p.status = 'success'
          WHERE 1=1";

if ($semester) {
    $query .= " AND s.current_semester = $semester";
}
if ($department) {
    $query .= " AND s.department_id = $department";
}
if ($payment_status) {
    if ($payment_status === 'paid') {
        $query .= " AND p.id IS NOT NULL";
    } else {
        $query .= " AND p.id IS NULL";
    }
}

$query .= " ORDER BY s.registration_number";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Success/Error Messages -->
    <?php if(isset($_GET['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 fixed top-4 right-4" role="alert">
        <?php 
        switch($_GET['success']) {
            case 'notice_added':
                echo 'Notice added successfully!';
                break;
            default:
                echo 'Operation completed successfully!';
        }
        ?>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 fixed top-4 right-4" role="alert">
        <?php 
        switch($_GET['error']) {
            case 'notice_failed':
                echo 'Failed to add notice. Please try again.';
                break;
            default:
                echo 'An error occurred. Please try again.';
        }
        ?>
    </div>
    <?php endif; ?>

    <div class="bg-blue-800 text-white py-4 px-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">KHANKIPUR UNIVERSITY</h1>
            <p class="text-lg">Admin Dashboard</p>
        </div>
        <button onclick="handleLogout()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Logout
        </button>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Notice Management Section -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Notice Management</h2>
                    <a href="add_notice.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-plus mr-2"></i>Add New Notice
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($notice = $notices->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php echo htmlspecialchars($notice['title']); ?>
                                        <?php if(strtotime($notice['created_at']) > strtotime('-7 days')): ?>
                                            <img src="../assets/blink.gif" alt="New" class="ml-2 h-4">
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('d-m-Y', strtotime($notice['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($notice['file_path']): ?>
                                        <a href="../<?php echo $notice['file_path']; ?>" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-<?php echo $notice['file_type'] == 'pdf' ? 'pdf' : 'image'; ?> mr-2"></i>
                                            View
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $notice['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="toggleNoticeStatus(<?php echo $notice['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-toggle-on mr-1"></i>Toggle Status
                                    </button>
                                    <button onclick="deleteNotice(<?php echo $notice['id']; ?>)" 
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <h2 class="text-lg font-bold mb-4">Filters</h2>
            <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Semester</label>
                    <select name="semester" class="w-full border rounded p-2">
                        <option value="">All Semesters</option>
                        <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $semester === $i ? 'selected' : ''; ?>>
                                Semester <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <select name="department" class="w-full border rounded p-2">
                        <option value="">All Departments</option>
                        <?php
                        $dept_query = "SELECT id, name FROM departments ORDER BY name";
                        $dept_result = $conn->query($dept_query);
                        while($dept = $dept_result->fetch_assoc()):
                        ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                <?php echo $department === $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full border rounded p-2">
                        <option value="">All Status</option>
                        <option value="paid" <?php echo $payment_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="pending" <?php echo $payment_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Export Button -->
        <div class="mb-4">
            <button onclick="exportToExcel()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Export to Excel
            </button>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full" id="studentsTable">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registration No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Semester
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment ID
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['registration_number']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['department_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['current_semester']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $row['payment_status'] === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $row['payment_status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $row['amount'] ? '₹'.number_format($row['amount'], 2) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $row['payment_date'] ? date('d-m-Y H:i', strtotime($row['payment_date'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $row['transaction_id'] ?? '-'; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function handleLogout() {
        if(confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    function exportToExcel() {
        const table = document.getElementById('studentsTable');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Students"});
        XLSX.writeFile(wb, 'students_payment_report.xlsx');
    }

    function toggleNoticeStatus(noticeId) {
        if(confirm('Are you sure you want to toggle this notice\'s status?')) {
            fetch('toggle_notice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notice_id=${noticeId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to toggle notice status');
                }
            });
        }
    }

    function deleteNotice(noticeId) {
        if(confirm('Are you sure you want to delete this notice? This action cannot be undone.')) {
            fetch('delete_notice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notice_id=${noticeId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete notice');
                }
            });
        }
    }

    // Auto-hide success/error messages after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 3000);
    </script>
</body>
</html> 