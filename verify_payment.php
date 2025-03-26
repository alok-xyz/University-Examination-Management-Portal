<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$student_id = $_SESSION['student_id'];

try {
    $api = new Api('rzp_test_ykJT9pz3eI8bEH', '5bTagqXDtzR4WW23MYyD6Xy2');
    $attributes = [
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_signature' => $data['razorpay_signature']
    ];

    $api->utility->verifyPaymentSignature($attributes);

    // Save payment details
    $query = "INSERT INTO payments (student_id, payment_id, amount, status) VALUES (?, ?, 300.00, 'success')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $student_id, $data['razorpay_payment_id']);
    $stmt->execute();

    // Set payment success flag in session
    $_SESSION['payment_success'] = true;

    echo json_encode([
        'success' => true,
        'redirect' => 'success.php'
    ]);
} catch(SignatureVerificationError $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 