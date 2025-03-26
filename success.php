<?php
session_start();
require_once 'config/database.php';

// Validate session and payment
if (!isset($_SESSION['student_id']) || !isset($_SESSION['payment_success'])) {
    header('Location: dashboard.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Verify payment in database
$payment_query = "SELECT * FROM payments WHERE student_id = ? AND status = 'success' ORDER BY created_at DESC LIMIT 1";
$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param("i", $student_id);
$payment_stmt->execute();
$payment = $payment_stmt->get_result()->fetch_assoc();

if (!$payment) {
    header('Location: dashboard.php');
    exit();
}

// Add cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-image: url('https://wallpaperboat.com/wp-content/uploads/2021/08/05/78116/iOS-13-liquid-02.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .success-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 90%;
            width: 400px;
            position: relative;
            overflow: hidden;
        }

        .success-header {
            background: #008000;
            color: white;
            margin: -2rem -2rem 1.5rem -2rem;
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 500;
        }

        .checkmark {
            width: 50px;
            height: 50px;
            background: #008000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .checkmark svg {
            width: 30px;
            height: 30px;
            fill: white;
        }

        .message {
            color: #333;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .sub-message {
            color: #dc2626;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .redirect-message {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .warning {
            color: #dc2626;
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-header">Payment Success !</div>
        <div class="checkmark">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <div class="message">Generating Payment Receipt & Admit Card !!</div>
        <div class="redirect-message">This page will redirect in <span id="countdown">5</span> seconds.</div>
        <div class="warning">Do Not Press Back or Refreash Button !!</div>
    </div>

    <script>
        // Prevent direct access to this page through browser history
        if (performance.navigation.type === 2) {
            window.location.replace('dashboard.php');
        }

        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.replace('print_receipt.php');
            }
        }, 1000);

        // Handle back button
        window.onpopstate = function() {
            window.location.replace('dashboard.php');
        };
    </script>
</body>
</html> 