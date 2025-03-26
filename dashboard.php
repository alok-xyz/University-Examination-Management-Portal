<?php
session_start();
require_once 'config/database.php';

// Basic session check
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Add cache control headers to prevent back button after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$student_id = $_SESSION['student_id'];
$semester = $_SESSION['semester'];

// Get student details
$student_query = "SELECT s.*, d.name as department_name 
                 FROM students s
                 LEFT JOIN departments d ON s.department_id = d.id
                 WHERE s.id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Check if payment is already made
$payment_query = "SELECT * FROM payments WHERE student_id = ? AND status = 'success' ORDER BY created_at DESC LIMIT 1";
$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param("i", $student_id);
$payment_stmt->execute();
$payment = $payment_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <style>
        /* Customize NProgress bar color to match the theme */
        #nprogress .bar {
            background: #82e0aa !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px #82e0aa, 0 0 5px #82e0aa !important;
        }
        #nprogress .spinner-icon {
            border-top-color: #82e0aa !important;
            border-left-color: #82e0aa !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Photo Upload Modal - Move to top -->
    <div id="photoUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Upload Photo</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Please upload your photo (max size: 100KB).<br>
                        Supported formats: JPG, PNG
                    </p>
                    <form id="photoUploadForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <input type="file" id="photo" name="photo" 
                                   accept="image/jpeg,image/png"
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                                   required>
                        </div>
                        <div id="photoPreview" class="mb-4 hidden">
                            <img id="previewImage" class="mx-auto max-h-40 rounded-lg">
                        </div>
                        <div id="errorMessage" class="text-red-500 text-sm mb-4 hidden"></div>
                        <button type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Upload Photo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- University Header -->
    <div class="bg-blue-800 text-white py-4 mb-6">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold text-center">KHANKIPUR UNIVERSITY</h1>
            <p class="text-center text-lg">
                <?php echo $student['program'] . ' Semester ' . $semester . ' Examination Form Fillup'; ?>
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Student Dashboard</h1>
                <a href="javascript:void(0)" onclick="handleLogout()" class="text-red-500 hover:text-red-700">Logout</a>
            </div>

            <!-- Student Information Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4 bg-gray-100 p-2">Student Information</h2>
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="md:w-1/4">
                        <img src="<?php echo !empty($student['photo_path']) ? $student['photo_path'] : 'assets/images/default-avatar.png'; ?>" 
                             alt="Student Photo" 
                             class="student-photo w-32 h-32 object-cover rounded-lg border-2 border-gray-200 mx-auto"
                             style="object-fit: cover;">
                        <?php if(empty($student['photo_path'])): ?>
                            <button onclick="showPhotoUploadModal()" 
                                    class="mt-2 text-sm text-blue-500 hover:text-blue-700 block mx-auto">
                                Upload Photo
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="md:w-3/4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Name:</div>
                            <div class="w-1/2"><?php echo $student['name']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Registration Number:</div>
                            <div class="w-1/2"><?php echo $student['registration_number']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Roll Number:</div>
                            <div class="w-1/2"><?php echo $student['roll_number']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Student Type:</div>
                            <div class="w-1/2 capitalize"><?php echo $student['student_type']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Course:</div>
                            <div class="w-1/2"><?php echo $student['course']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Current Semester:</div>
                            <div class="w-1/2"><?php echo $student['current_semester']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Department:</div>
                            <div class="w-1/2"><?php echo htmlspecialchars($student['department_name'] ?? 'Not Assigned'); ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Program:</div>
                            <div class="w-1/2"><?php echo $student['program']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Mobile:</div>
                            <div class="w-1/2"><?php echo $student['mobile_number']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Email:</div>
                            <div class="w-1/2"><?php echo $student['email']; ?></div>
                        </div>
                        
                        <div class="flex">
                            <div class="w-1/2 text-gray-600">Father's Name:</div>
                            <div class="w-1/2"><?php echo $student['fathers_name']; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Examination Details Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4 bg-gray-100 p-2">Examination Details</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left border" style="width: 60px;">Sl No.</th>
                                <th class="px-4 py-2 text-left border whitespace-nowrap" style="width: 120px;">Paper Code</th>
                                <th class="px-4 py-2 text-left border">Paper Name</th>
                                <th class="px-4 py-2 text-center border" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $slno = 1;
                            for($i = 1; $i <= 8; $i++): 
                                $code_field = "paper{$i}_code";
                                $name_field = "paper{$i}_name";
                                
                                if(!empty($student[$code_field])): 
                            ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 border"><?php echo $slno++; ?></td>
                                    <td class="px-4 py-2 border whitespace-nowrap">
                                        <?php echo htmlspecialchars($student[$code_field]); ?>
                                    </td>
                                    <td class="px-4 py-2 border">
                                        <?php echo htmlspecialchars($student[$name_field]); ?>
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="checkbox" name="selected_papers[]" 
                                               value="<?php echo $i; ?>" 
                                               checked
                                               onclick="return false;"
                                               class="paper-checkbox form-checkbox h-4 w-4 text-blue-300 rounded border-gray-300 cursor-not-allowed">
                                    </td>
                                </tr>
                            <?php 
                                endif;
                            endfor; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment/Download Section -->
            <div class="text-center">
                <?php if($payment): ?>
                    <a href="print_receipt.php" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 inline-block">
                        Download Admit Card
                    </a>
                <?php else: ?>
                    <div class="max-w-md mx-auto bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="confirmCheck" class="mr-2">
                            <label for="confirmCheck" class="text-sm text-gray-700">
                                I confirm that all the above information is correct and I want to proceed with the payment of ₹300
                            </label>
                        </div>
                        <button onclick="handlePayment()" 
                                id="payButton" 
                                disabled 
                                class="w-full bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Proceed to Pay Fees (₹300)
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>
    <script>
        // Start progress bar when page starts loading
        NProgress.start();
        
        // Complete the progress bar when page finishes loading
        window.onload = function() {
            NProgress.done();
        }

        // Add this at the beginning of your script section
        $(document).ready(function() {
            // Handle checkbox for payment confirmation
            $('#confirmCheck').on('change', function() {
                $('#payButton').prop('disabled', !this.checked);
            });
        });

        function handlePayment() {
            if (!$('#confirmCheck').prop('checked')) {
                alert('Please confirm by checking the checkbox first');
                return;
            }
            
            // Get selected papers
            const selectedPapers = [];
            for(let i = 1; i <= 8; i++) {
                if($(`input[value="${i}"]`).prop('checked')) {
                    selectedPapers.push(i);
                }
            }
            
            // Store selected papers in session before payment
            $.ajax({
                url: 'store_session.php',
                method: 'POST',
                data: {
                    action: 'payment_initiated',
                    selected_papers: selectedPapers
                },
                success: function() {
                    window.location.href = 'payment.php';
                }
            });
        }

        // Check session status periodically
        function checkSession() {
            $.ajax({
                url: 'check_session.php',
                method: 'GET',
                success: function(response) {
                    if (!response.valid) {
                        alert('Your session has expired. Please login again.');
                        window.location.href = 'index.php?error=session_expired';
                    }
                }
            });
        }

        // Check session every 5 minutes
        setInterval(checkSession, 300000);

        function handleLogout() {
            // First clear any local storage and session storage
            localStorage.clear();
            sessionStorage.clear();
            
            // Set a flag to prevent redirect loops
            sessionStorage.setItem('logging_out', 'true');
            
            // Make an AJAX call to clear_session.php
            fetch('clear_session.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Force reload the index page
                    window.location.replace('index.php?logout=true');
                }
            })
            .catch(() => {
                // Fallback redirect
                window.location.replace('index.php?logout=true');
            });
            
            return false;
        }

        // Check if student photo exists
        $(document).ready(function() {
            <?php if(empty($student['photo_path']) || !file_exists($student['photo_path'])): ?>
                showPhotoUploadModal();
            <?php endif; ?>
        });

        function showPhotoUploadModal() {
            $('#photoUploadModal').removeClass('hidden');
        }

        // Photo preview and validation
        $('#photo').on('change', function(e) {
            const file = e.target.files[0];
            const errorMessage = $('#errorMessage');
            const photoPreview = $('#photoPreview');
            const previewImage = $('#previewImage');
            
            errorMessage.addClass('hidden');
            
            if (file) {
                // Check file size (100KB = 102400 bytes)
                if (file.size > 102400) {
                    errorMessage.text('File size must be less than 100KB').removeClass('hidden');
                    this.value = '';
                    photoPreview.addClass('hidden');
                    return;
                }

                // Check file type
                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    errorMessage.text('Only JPG and PNG files are allowed').removeClass('hidden');
                    this.value = '';
                    photoPreview.addClass('hidden');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.attr('src', e.target.result);
                    photoPreview.removeClass('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle photo upload
        $('#photoUploadForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            NProgress.start();
            
            $.ajax({
                url: 'upload_photo.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            $('.student-photo').attr('src', data.photo_path);
                            $('#photoUploadModal').addClass('hidden');
                            alert('Photo uploaded successfully!');
                            // Refresh the page after successful upload
                            window.location.reload();
                        } else {
                            $('#errorMessage').text(data.message).removeClass('hidden');
                        }
                    } catch(e) {
                        $('#errorMessage').text('Error processing response').removeClass('hidden');
                    }
                },
                error: function() {
                    $('#errorMessage').text('Error uploading photo. Please try again.').removeClass('hidden');
                },
                complete: function() {
                    NProgress.done();
                }
            });
        });

        // Auto logout functionality
        let inactivityTimer;
        const TIMEOUT_DURATION = 60000; // 60 seconds in milliseconds

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(autoLogout, TIMEOUT_DURATION);
        }

        function autoLogout() {
            // Clear any local storage or session data
            localStorage.clear();
            
            // Directly redirect without showing alert
            window.location.replace('index.php'); // Using replace to prevent back button
        }

        // Reset timer on user activity
        const activityEvents = [
            'mousedown', 'mousemove', 'keydown',
            'scroll', 'touchstart', 'click', 'keypress'
        ];

        activityEvents.forEach(function(eventName) {
            document.addEventListener(eventName, resetTimer, true);
        });

        // Start the timer when page loads
        resetTimer();

        // Also reset timer on AJAX requests
        $(document).ajaxStart(function() {
            resetTimer();
        });

        // Prevent multiple tabs/windows
        const sessionId = Math.random().toString(36).substring(2);
        localStorage.setItem('sessionId', sessionId);

        window.addEventListener('storage', function(e) {
            if (e.key === 'sessionId' && e.newValue !== sessionId) {
                autoLogout();
            }
        });

        // Configure NProgress
        NProgress.configure({ 
            showSpinner: true,
            minimum: 0.1,
            easing: 'ease',
            speed: 500
        });

        // Show progress bar on all AJAX requests
        $(document).ajaxStart(function() {
            NProgress.start();
        });

        $(document).ajaxComplete(function() {
            NProgress.done();
        });

        // Show progress bar on page navigation
        $(document).on('click', 'a', function() {
            NProgress.start();
        });
    </script>
</body>
</html>