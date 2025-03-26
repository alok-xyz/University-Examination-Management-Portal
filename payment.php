<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// First check if payment is already made
$payment_check_query = "SELECT * FROM payments WHERE student_id = ? AND status = 'success' ORDER BY created_at DESC LIMIT 1";
$payment_check_stmt = $conn->prepare($payment_check_query);
$payment_check_stmt->bind_param("i", $student_id);
$payment_check_stmt->execute();
$existing_payment = $payment_check_stmt->get_result();

// If payment exists, redirect to print_receipt
if ($existing_payment->num_rows > 0) {
    $_SESSION['payment_success'] = true;
    header('Location: print_receipt.php');
    exit();
}

// Continue with rest of the payment page code only if no payment exists
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Initialize Razorpay
$api = new Api('key', 'key');

// Create order
$orderData = [
    'receipt' => 'rcpt_' . $student_id . '_' . time(),
    'amount' => 300 * 100, // Amount in paise
    'currency' => 'INR'
];

$razorpayOrder = $api->order->create($orderData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - KU Exam Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Payment Details</h1>
            
            <!-- Add this payment verification section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h2 class="text-lg font-semibold mb-2">Previous Payment Verification</h2>
                <p class="text-sm text-gray-600 mb-3">If you've already made the payment but got disconnected, click below to verify:</p>
                <button id="verifyPayment" class="w-full bg-indigo-500 text-white py-2 rounded-lg hover:bg-indigo-600 mb-2">
                    Verify Previous Payment
                </button>
                <div id="verificationMessage" class="text-sm hidden"></div>
            </div>

            <div class="mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-gray-600">Name:</div>
                    <div><?php echo $student['name']; ?></div>
                    
                    <div class="text-gray-600">Registration Number:</div>
                    <div><?php echo $student['registration_number']; ?></div>
                    
                    <div class="text-gray-600">Amount:</div>
                    <div>₹300</div>
                </div>
            </div>

            <button id="payButton" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                Pay Now
            </button>
        </div>
    </div>

    <script>
        // Add this before existing script
        document.getElementById('verifyPayment').addEventListener('click', function() {
            const messageDiv = document.getElementById('verificationMessage');
            messageDiv.classList.remove('hidden');
            messageDiv.innerHTML = '<div class="text-center text-gray-600">Checking payment status...</div>';
            
            fetch('verify_previous_payment.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="text-center text-green-600">Payment found! Redirecting to receipt page...</div>';
                        setTimeout(() => {
                            window.location.href = 'print_receipt.php';
                        }, 1500);
                    } else {
                        messageDiv.innerHTML = '<div class="text-center text-red-600">' + data.message + '</div>';
                    }
                })
                .catch(() => {
                    messageDiv.innerHTML = '<div class="text-center text-red-600">Error checking payment status. Please try again.</div>';
                });
        });

        var options = {
            "key": "rzp_test_ykJT9pz3eI8bEH",
            "amount": "<?php echo $orderData['amount']; ?>",
            "currency": "INR",
            "name": "Khankipur University",
            "description": "Exam Fee Payment",
            "order_id": "<?php echo $razorpayOrder->id; ?>",
            "handler": function (response) {
                // Send payment details to server
                fetch('verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert('Payment verification failed!');
                    }
                });
            },
            "prefill": {
                "name": "<?php echo $student['name']; ?>",
                "email": "<?php echo $student['email']; ?>",
                "contact": "<?php echo $student['mobile_number']; ?>"
            },
            "theme": {
                "color": "#3B82F6"
            }
        };
        
        document.getElementById('payButton').onclick = function(e) {
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>
</html> 
