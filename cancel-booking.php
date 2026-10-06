<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit();
}

try {
    // Check if booking belongs to user
    $check_sql = "SELECT * FROM tour_guide_bookings WHERE id = ? AND user_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$data['booking_id'], $_SESSION['user_id']]);
    $booking = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit();
    }
    
    // Update booking status
    $update_sql = "UPDATE tour_guide_bookings SET status = 'cancelled' WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$data['booking_id']]);
    
    // Update guide availability
    $guide_sql = "UPDATE tour_guides SET is_available = 1 WHERE id = ?";
    $guide_stmt = $pdo->prepare($guide_sql);
    $guide_stmt->execute([$booking['guide_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>