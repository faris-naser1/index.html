<?php
// process_booking.php
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $match_id = (int)$_POST['match_id'];
    $seat_category = $_POST['seat_category'];
    $quantity = (int)$_POST['quantity'];
    $payment_method = $_POST['payment_method'];
    
    try {
        // Get match details
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
        $stmt->execute([$match_id]);
        $match = $stmt->fetch();
        
        if (!$match) {
            throw new Exception('Match not found');
        }
        
        // Calculate price
        $prices = [
            'standard' => $match['base_price'],
            'premium' => $match['base_price'] * 1.5,
            'vip' => $match['base_price'] * 2,
            'corporate' => $match['base_price'] * 3
        ];
        
        $unit_price = $prices[$seat_category] ?? $match['base_price'];
        $total_amount = $unit_price * $quantity;
        
        // Process payment (simulated)
        $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
        
        // Insert ticket
        $stmt = $pdo->prepare("INSERT INTO tickets (
            user_id, match_id, team1, team2, match_date, venue,
            seat_category, quantity, unit_price, total_amount,
            payment_method, transaction_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $user_id, $match_id, $match['team1'], $match['team2'], $match['match_date'], $match['stadium'],
            $seat_category, $quantity, $unit_price, $total_amount,
            $payment_method, $transaction_id
        ]);
        
        $ticket_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'ticket_id' => $ticket_id,
            'transaction_id' => $transaction_id,
            'total_amount' => $total_amount,
            'message' => 'Ticket booked successfully!'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?>