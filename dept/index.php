<?php
session_start();
if(isset($_SESSION['dept_id'])) {
    header('Location: dept_dashboard.php');
    exit();
}
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Login - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0f2ff 0%, #e8fff3 50%, #d5f3ff 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="login-card rounded-lg p-8">
                <div class="text-center mb-8">
                    <img src="../assets/images/university-logo.png" alt="University Logo" class="mx-auto mb-4" style="max-width: 150px;">
                    <h1 class="text-2xl font-bold text-gray-800">KHANKIPUR UNIVERSITY</h1>
                    <p class="text-gray-600">Department Login</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-700">
                    <?php 
                    switch($_GET['error']) {
                        case 'invalid_credentials':
                            echo 'Invalid department ID or password.';
                            break;
                        case 'session_expired':
                            echo 'Your session has expired. Please login again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="verify_dept_login.php">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="department">
                                Select Department
                            </label>
                            <select name="department" id="department" required 
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="">Select Department</option>
                                <?php
                                $dept_query = "SELECT id, name FROM departments ORDER BY name";
                                $dept_result = $conn->query($dept_query);
                                while($dept = $dept_result->fetch_assoc()):
                                ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="login_id">
                                Login ID
                            </label>
                            <input type="text" id="login_id" name="login_id" required
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                                Password
                            </label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>

                        <button type="submit" 
                            class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                            Login
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                        Back to Student Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 