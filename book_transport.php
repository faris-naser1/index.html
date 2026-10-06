<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $transport_type = $_POST['transport_type'];
    $departure_date = $_POST['departure_date'];
    $departure_time = $_POST['departure_time'];
    $from_location = $_POST['from_location'];
    $to_location = $_POST['to_location'];
    $quantity = $_POST['quantity'];
    $total_price = $_POST['total_amount'];
    
    $stmt = $pdo->prepare("INSERT INTO transportation (user_id, transport_type, departure_date, departure_time, from_location, to_location, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$user_id, $transport_type, $departure_date, $departure_time, $from_location, $to_location, $quantity, $total_price])) {
        $transport_id = $pdo->lastInsertId();
        $_SESSION['booking_success'] = 'Transport booked successfully! Booking ID: ' . $transport_id;
        header('Location: ../payment.html?type=transport&id=' . $transport_id . '&amount=' . $total_price);
        exit;
    } else {
        $_SESSION['error'] = 'Failed to book transport. Please try again.';
        header('Location: ../transport.html');
        exit;
    }
}
?>