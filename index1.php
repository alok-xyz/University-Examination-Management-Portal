<?php
require_once 'config/database.php';

// Fetch latest notices
$notice_query = "SELECT * FROM notices WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10";
$notices = $conn->query($notice_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Khankipur University</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #fff;
        }
        .hero-section {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%);
            min-height: 600px;
        }
        .login-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .login-circle:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .login-icon {
            width: 60px;
            height: 60px;
            background: #4F46E5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .notice-card {
            border-left: 4px solid #4F46E5;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
        }
        .notice-card:hover {
            transform: translateX(5px);
        }
        .top-nav {
            background: rgba(255, 255, 255, 0.1);
        }
        .notice-scroll {
            max-height: 300px; /* Height for 3 notices approximately */
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #93C5FD transparent;
        }
        .notice-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .notice-scroll::-webkit-scrollbar-track {
            background: #F3F4F6;
            border-radius: 4px;
        }
        .notice-scroll::-webkit-scrollbar-thumb {
            background-color: #93C5FD;
            border-radius: 4px;
            border: 2px solid #F3F4F6;
        }
        .notice-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #60A5FA;
        }
        .notice-item {
            padding: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }
        .notice-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="bg-indigo-600 text-white py-2 px-4">
        <div class="container mx-auto text-center">
            <span><i class="fas fa-phone-alt mr-2"></i>Helpline Number : 7908858735</span>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section relative">
        <!-- Navigation -->
        <nav class="top-nav">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-center">
                    <div class="flex items-center">
                        <img src="assets/images/university-logo.png" alt="Logo" class="h-16">
                        <div class="ml-4 text-white text-center">
                            <a href="index1.php" class="hover:text-indigo-200 transition-colors">
                                <h1 class="text-3xl font-bold">KHANKIPUR UNIVERSITY</h1>
                                <p class="text-lg">Examination Department</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Notice Board in Hero Section -->
        <div class="container mx-auto px-4 py-8">
            <div class="text-white mb-6">
                <h2 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-bullhorn mr-3"></i>Notice Board
                </h2>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="notice-scroll">
                    <?php if($notices->num_rows > 0): ?>
                        <?php while($notice = $notices->fetch_assoc()): ?>
                            <div class="notice-item hover:bg-blue-50 transition-colors">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <?php if($notice['file_type'] == 'pdf'): ?>
                                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                        <?php elseif(in_array($notice['file_type'], ['jpg', 'jpeg', 'png'])): ?>
                                            <i class="fas fa-file-image text-blue-500 text-xl"></i>
                                        <?php else: ?>
                                            <i class="fas fa-bell text-blue-500 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4 flex-grow">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-blue-600 font-semibold hover:text-blue-800 cursor-pointer flex items-center">
                                                <?php echo htmlspecialchars($notice['title']); ?>
                                                <?php if(strtotime($notice['created_at']) > strtotime('-7 days')): ?>
                                                    <img src="assets/blink.gif" alt="New" class="ml-2 h-4">
                                                <?php endif; ?>
                                            </h3>
                                            <span class="text-sm text-gray-500">
                                                <?php echo date('d-m-Y', strtotime($notice['created_at'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600 text-sm mt-1">
                                            <?php echo htmlspecialchars($notice['content']); ?>
                                        </p>
                                        <?php if($notice['file_path']): ?>
                                            <div class="mt-2">
                                                <a href="<?php echo htmlspecialchars($notice['file_path']); ?>" 
                                                   target="_blank"
                                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-download mr-2"></i>
                                                    Download <?php echo strtoupper($notice['file_type']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            No notices available at the moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Circles Section -->
    <div class="container mx-auto px-4 -mt-20">
        <div class="flex flex-wrap justify-center gap-8">
            <!-- Admin Login Circle -->
            <div class="login-circle flex flex-col items-center justify-center p-6 cursor-pointer"
                 onclick="window.location.href='admin/index.php'">
                <div class="login-icon">
                    <i class="fas fa-user-shield text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">COE Login</h3>
            </div>

            <!-- Department Login Circle -->
            <div class="login-circle flex flex-col items-center justify-center p-6 cursor-pointer"
                 onclick="window.location.href='dept/index.php'">
                <div class="login-icon">
                    <i class="fas fa-building text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Department's Login</h3>
            </div>

            <!-- Student Login Circle -->
            <div class="login-circle flex flex-col items-center justify-center p-6 cursor-pointer"
                 onclick="window.location.href='index.php'">
                <div class="login-icon">
                    <i class="fas fa-user-graduate text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Student's Login</h3>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-indigo-300">About Us</a></li>
                        <li><a href="#" class="hover:text-indigo-300">Contact Us</a></li>
                        <li><a href="#" class="hover:text-indigo-300">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-indigo-300">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Contact Info</h3>
                    <ul class="space-y-2">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Khankipur, West Bengal</li>
                        <li><i class="fas fa-phone mr-2"></i> +91 7908858735</li>
                        <li><i class="fas fa-envelope mr-2"></i> info@kuexam.ac.in</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-indigo-300"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="hover:text-indigo-300"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="hover:text-indigo-300"><i class="fab fa-youtube fa-2x"></i></a>
                        <a href="#" class="hover:text-indigo-300"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-8 pt-8 border-t border-gray-700">
                <p>© <?php echo date('Y'); ?> Khankipur University Examination Department. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 