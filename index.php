<?php
session_start();

// Add maintenance mode switch
$maintenance_mode = 0; // 0 = closed, 1 = open

if ($maintenance_mode === 1) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>KU Exam Portal - Closed</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <script>
            // Disable right click
            document.addEventListener("contextmenu", function(e) {
                e.preventDefault();
            });

            // Disable keyboard shortcuts
            document.addEventListener("keydown", function(e) {
                // Disable F12
                if(e.keyCode == 123) {
                    e.preventDefault();
                }
                
                // Disable Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
                if(e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 85)) {
                    e.preventDefault();
                }
            });
        </script>
        <style>
            body {
                background: linear-gradient(135deg, #8B5CF6 0%, #C4B5FD 50%, #ffffff 100%);
                min-height: 100vh;
                background-attachment: fixed;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            .glass-container {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            }
        </style>
    </head>
    <body>
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto glass-container rounded-lg p-6">
                <div class="text-center mb-8">
                    <img src="assets/images/university-logo.png" alt="University Logo" class="mx-auto mb-4" style="max-width: 150px;">
                    <h1 class="text-2xl font-bold text-gray-800">Khankipur University</h1>
                    <p class="text-red-600 font-bold mt-4">The Exam Form fill-up portal is now closed. Thank you for your cooperation. 
                    <br>For any queries or exam-related issues, please contact the Controller Branch during office hours.</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit();
}

// If there's any existing session, destroy it
if (isset($_SESSION['student_id'])) {
    session_destroy();
    $_SESSION = array();
}

// Add cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khankipur University Exam Portal (V1)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css" rel="stylesheet">
    <script>
        // Disable right click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Disable keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Disable F12
            if(e.keyCode == 123) {
                e.preventDefault();
            }
            
            // Disable Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
            if(e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 85)) {
                e.preventDefault();
            }
        });

        // Simpler DevTools detection without auto-refresh
        let devtools = {
            isOpen: false,
            orientation: undefined
        };
        
        // Check if DevTools is open
        setInterval(() => {
            const widthThreshold = window.outerWidth - window.innerWidth > 160;
            const heightThreshold = window.outerHeight - window.innerHeight > 160;
            
            devtools.isOpen = widthThreshold || heightThreshold;
            
            if(devtools.isOpen) {
                // Optional: You can add a warning message here instead of refreshing
                console.clear();
            }
        }, 1000);
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #8B5CF6 0%, #C4B5FD 50%, #ffffff 100%);
            min-height: 100vh;
            background-attachment: fixed;
        }
        .helpline-text {
            color: #DC2626; /* Red color */
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
            margin-top: 20px;
        }
        /* Add glass effect to the main container */
        .glass-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        /* Disable text selection */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection in input fields */
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto glass-container rounded-lg p-6">
            <div class="text-center mb-8">
                <img src="assets/images/university-logo.png" alt="University Logo" class="mx-auto mb-4" style="max-width: 150px;">
                <h1 class="text-2xl font-bold text-gray-800">Khankipur University</h1>
                <p class="text-gray-600">Exam Portal Login</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="mb-4 p-3 rounded-lg <?php 
                    if($_GET['error'] === 'attendance_pending') {
                        echo 'bg-red-100 text-red-700';
                    } else {
                        echo 'bg-yellow-100 text-yellow-700';
                    }
                ?>">
                    <?php 
                    switch($_GET['error']) {
                        case 'invalid_registration':
                            echo 'Invalid registration number. Please check and try again.';
                            break;
                        case 'wrong_password':
                            echo 'Incorrect date of birth. Please check and try again.';
                            break;
                        case 'invalid_credentials':
                            echo 'Invalid registration number or date of birth.';
                            break;
                        case 'attendance_pending':
                            echo 'Not eligible for form fill up, attendance pending. Please contact your department.';
                            break;
                        case 'semester_inactive':
                            echo 'Selected semester is not currently active for form fillup.';
                            break;
                        case 'invalid_semester':
                            $current = isset($_GET['current']) ? $_GET['current'] : '';
                            echo 'You are not eligible for the selected semester. Your current semester is ' . $current . ' As per University Data';
                            break;
                        case 'session_timeout':
                            echo 'Your session has expired due to inactivity. Please login again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="verify_login.php">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="registration">
                        Registration Number
                    </label>
                    <input type="text" id="registration" name="registration" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="semester">
                        Semester
                    </label>
                    <?php
                    // Get active semesters
                    $semester_query = "SELECT semester FROM active_semesters WHERE is_active = 1 ORDER BY semester";
                    $semester_result = $conn->query($semester_query);
                    
                    if ($semester_result->num_rows > 0) {
                    ?>
                        <select id="semester" name="semester" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Select Semester</option>
                            <?php while($row = $semester_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['semester']; ?>">Semester <?php echo $row['semester']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    <?php 
                    } else {
                        echo '<div class="text-red-600 text-sm">No active semesters available for form fillup.</div>';
                    }
                    ?>
                </div>

                <button type="button" id="getDetails" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 mb-4">
                    Get Details
                </button>

                <div id="studentDetails" class="hidden mb-4">
                    <p class="font-bold mb-2">Student Name: <span id="studentName"></span></p>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dob">
                            Date of Birth (Password)
                        </label>
                        <input type="date" id="dob" name="dob" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">
                        Login
                    </button>
                </div>

                <!-- Helpline Information -->
                <div class="mt-6 text-center" style="color: #DC2626;">
                    <p class="font-bold text-sm">For any technical & payment related issue contact us in our Helpline No: 7908858735 or Visit Controller Branch</p>
                    <p class="text-sm">(From 10:30AM - 05PM In Working Days)</p>
                </div>
            </form>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <div id="loadingOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <img src="assets/loading.gif" alt="Loading..." class="w-32 h-32">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>
    <script src="assets/js/login.js"></script>
    <script>
        // Show progress bar on page load
        $(document).ready(function() {
            NProgress.start();
        });

        // Hide progress bar when everything is loaded
        $(window).on('load', function() {
            NProgress.done();
        });

        // Show progress bar on page refresh
        window.onbeforeunload = function() {
            NProgress.start();
        };
    </script>
</body>
</html> 