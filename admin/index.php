<?php
session_start();
if(isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #fff0f3 50%, #f2f0ff 100%);
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
                    <p class="text-gray-600">Admin Login</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-700">
                    <?php 
                    switch($_GET['error']) {
                        case 'invalid_credentials':
                            echo 'Invalid username or password.';
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

                <form method="POST" action="verify_admin_login.php">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                                Username
                            </label>
                            <input type="text" id="username" name="username" required
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