<?php
// config.php

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===== DATABASE CONNECTION ===== */
$host = "localhost";
$db = "ticketing_system";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
/* ===== HELPER FUNCTIONS ===== */

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Set alert message
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get alert message
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Process payment (simulated for demo)
function processPayment($amount, $method) {
    // In a real application, integrate with payment gateway here
    // For demo, we simulate successful payment
    $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
    return [
        'success' => true,
        'transaction_id' => $transaction_id,
        'amount' => $amount,
        'method' => $method,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}



function getHotelLocations() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT DISTINCT location FROM hotels WHERE available_rooms > 0 ORDER BY location");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error fetching locations: " . $e->getMessage());
        return [];
    }
}
// Get match by ID
function getMatchById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching match: " . $e->getMessage());
        return null;
    }
}
/* ===== HOTEL-RELATED FUNCTIONS ===== */

// Get hotel by ID
function getHotelById($hotel_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->execute([$hotel_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching hotel: " . $e->getMessage());
        return null;
    }
}

// Get all available hotels
function getAvailableHotels() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM hotels WHERE available_rooms > 0 ORDER BY rating DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching hotels: " . $e->getMessage());
        return [];
    }
}
function getAllMatches() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM matches WHERE match_date >= CURDATE() ORDER BY match_date ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching matches: " . $e->getMessage());
        return [];
    }
}
// Create hotel booking
function createHotelBooking($booking_data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO hotel_bookings 
            (user_id, hotel_id, hotel_name, location, room_type, check_in, check_out, 
             guests, nights, price_per_night, total_amount, payment_method, 
             transaction_id, status, booking_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())
        ");
        
        return $stmt->execute([
            $booking_data['user_id'],
            $booking_data['hotel_id'],
            $booking_data['hotel_name'],
            $booking_data['location'],
            $booking_data['room_type'],
            $booking_data['check_in'],
            $booking_data['check_out'],
            $booking_data['guests'],
            $booking_data['nights'],
            $booking_data['price_per_night'],
            $booking_data['total_amount'],
            $booking_data['payment_method'],
            $booking_data['transaction_id']
        ]);
        
    } catch (Exception $e) {
        error_log("Error creating hotel booking: " . $e->getMessage());
        return false;
    }
}


// Get all users from database
function getAllUsers() {
    global $pdo;
   try {
    $sql = "SELECT `id`, `username`, `email`, `password`, `full_name`, `phone`, `created_at`, `first_name`, `last_name`, `date_of_birth`, `country`, `address`, `newsletter_subscribed`, `account_status`, `last_login`, `profile_image` FROM `users` WHERE 1";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        $users = [];
        $noUsersMessage = "No users found in the database.";
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $users = [];
}
}
// Update available rooms after booking
function updateHotelRooms($hotel_id, $rooms_change) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE hotels 
            SET available_rooms = available_rooms + ? 
            WHERE id = ? AND available_rooms + ? >= 0
        ");
        return $stmt->execute([$rooms_change, $hotel_id, $rooms_change]);
    } catch (Exception $e) {
        error_log("Error updating hotel rooms: " . $e->getMessage());
        return false;
    }
}

?>