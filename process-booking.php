<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to book a guide']);
    exit();
}

// Validate required fields
$required_fields = ['guide_id', 'booking_date', 'start_time', 'hours', 'group_size', 
                   'contact_name', 'contact_email', 'contact_phone'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit();
    }
}

try {
    // Get guide details
    $guide_id = $_POST['guide_id'];
    $guide_sql = "SELECT * FROM tour_guides WHERE id = ?";
    $guide_stmt = $pdo->prepare($guide_sql);
    $guide_stmt->execute([$guide_id]);
    $guide = $guide_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$guide) {
        echo json_encode(['success' => false, 'message' => 'Guide not found']);
        exit();
    }
    
    // Calculate total amount
    $hours = $_POST['hours'];
    $total_amount = $hours * $guide['hourly_rate'];
    
    // Calculate end time
    $start_time = $_POST['start_time'];
    $start_datetime = new DateTime($start_time);
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $hours . 'H'));
    $end_time = $end_datetime->format('H:i:s');
    
    // Prepare booking data
    $booking_data = [
        'user_id' => $_SESSION['user_id'],
        'guide_id' => $guide_id,
        'booking_date' => $_POST['booking_date'],
        'start_time' => $start_time,
        'end_time' => $end_time,
        'hours' => $hours,
        'total_amount' => $total_amount,
        'group_size' => $_POST['group_size'],
        'special_requirements' => $_POST['special_requirements'] ?? '',
        'contact_name' => $_POST['contact_name'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cash' // Default payment method
    ];
    
    // Insert booking into database
    $sql = "INSERT INTO tour_guide_bookings 
            (user_id, guide_id, booking_date, start_time, end_time, hours, total_amount, 
             group_size, special_requirements, contact_name, contact_email, contact_phone,
             status, payment_status, payment_method) 
            VALUES 
            (:user_id, :guide_id, :booking_date, :start_time, :end_time, :hours, :total_amount,
             :group_size, :special_requirements, :contact_name, :contact_email, :contact_phone,
             :status, :payment_status, :payment_method)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($booking_data);
    
    $booking_id = $pdo->lastInsertId();
    
    // Update guide's availability if needed
    $update_sql = "UPDATE tour_guides SET is_available = 0 WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$guide_id]);
    
    // Send confirmation email (in production)
    // sendConfirmationEmail($booking_data, $booking_id, $guide);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'total_amount' => $total_amount
    ]);
    
} catch (PDOException $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>