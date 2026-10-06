<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_type = $_POST['booking_type'];
    $booking_id = $_POST['booking_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = uniqid('TRX_', true);
    
    // In a real application, you would integrate with a payment gateway here
    // For demo purposes, we'll simulate a successful payment
    
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, booking_type, booking_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?, 'completed', ?)");
    
    if ($stmt->execute([$user_id, $booking_type, $booking_id, $amount, $payment_method, $transaction_id])) {
        $_SESSION['payment_success'] = [
            'transaction_id' => $transaction_id,
            'amount' => $amount,
            'booking_type' => $booking_type
        ];
        header('Location: ../payment-success.html');
        exit;
    } else {
        $_SESSION['error'] = 'Payment failed. Please try again.';
        header('Location: ../payment.html');
        exit;
    }
}
?>