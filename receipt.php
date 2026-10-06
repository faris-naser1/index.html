<?php
// receipt.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($type !== 'transport' || $id <= 0) {
    die('Invalid receipt request');
}

// Get booking details
$query = "SELECT tb.*, u.username, u.email, u.full_name 
          FROM transport_bookings tb
          JOIN users u ON tb.user_id = u.id
          WHERE tb.id = '$id' AND tb.user_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    die('Booking not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Receipt - MatchTicketing</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .receipt { max-width: 600px; margin: 0 auto; border: 1px solid #ccc; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1a237e; }
        .details { margin: 20px 0; }
        .row { display: flex; justify-content: space-between; margin: 10px 0; }
        .total { font-size: 1.2em; font-weight: bold; border-top: 2px solid #000; padding-top: 10px; }
        .footer { text-align: center; margin-top: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>MatchTicketing Transport Receipt</h1>
            <p>Booking Reference: TRP<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
            <p>Date: <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
        </div>
        
        <div class="details">
            <div class="row">
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($booking['full_name']); ?></span>
            </div>
            <div class="row">
                <span>Email:</span>
                <span><?php echo htmlspecialchars($booking['email']); ?></span>
            </div>
            <div class="row">
                <span>Transport Type:</span>
                <span><?php echo ucfirst(str_replace('_', ' ', $booking['transport_type'])); ?></span>
            </div>
            <div class="row">
                <span>Route:</span>
                <span><?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?></span>
            </div>
            <div class="row">
                <span>Date & Time:</span>
                <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?> at <?php echo $booking['departure_time']; ?></span>
            </div>
            <div class="row">
                <span>Estimated Arrival:</span>
                <span><?php echo $booking['estimated_arrival']; ?></span>
            </div>
            <div class="row">
                <span>Passengers:</span>
                <span><?php echo $booking['quantity']; ?></span>
            </div>
            <div class="row">
                <span>Price per Ticket:</span>
                <span>$<?php echo number_format($booking['price_per_ticket'], 2); ?></span>
            </div>
        </div>
        
        <div class="total row">
            <span>Total Amount:</span>
            <span>$<?php echo number_format($booking['total_amount'], 2); ?></span>
        </div>
        
        <div class="footer">
            <p>Thank you for booking with MatchTicketing!</p>
            <p>For inquiries, contact support@matchticketing.com</p>
            <p>This is an electronic receipt. No signature required.</p>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>