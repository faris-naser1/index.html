<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's bookings
$sql = "SELECT b.*, g.name as guide_name, g.profile_image, g.country, g.city, 
               g.hourly_rate, g.email as guide_email, g.phone as guide_phone
        FROM tour_guide_bookings b
        JOIN tour_guides g ON b.guide_id = g.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC, b.created_at DESC";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - MatchTicketing</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bookings-container {
            max-width: 1200px;
            margin: 80px auto 30px;
            padding: 20px;
        }
        
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-id {
            font-weight: bold;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .guide-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .guide-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .guide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .detail-item {
            margin-bottom: 8px;
        }
        
        .detail-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .detail-value {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .total-amount {
            text-align: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 15px 0;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: #3498db;
            color: white;
        }
        
        .btn-view:hover {
            background: #2980b9;
        }
        
        .btn-cancel {
            background: #f8f9fa;
            color: #e74c3c;
            border: 1px solid #ddd;
        }
        
        .btn-cancel:hover {
            background: #f1f1f1;
        }
        
        .no-bookings {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-bookings i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation (same as other pages) -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">
                <i class="fas fa-ticket-alt"></i>
                <span>MatchTicketing</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Match Tickets</a></li>
                <li><a href="hotels.php"><i class="fas fa-hotel"></i> Hotels</a></li>
                <li><a href="transport.php"><i class="fas fa-bus"></i> Transport</a></li>
                <li><a href="tour-guides.php"><i class="fas fa-user-friends"></i> Tour Guides</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="my-bookings.php" class="active"><i class="fas fa-calendar-alt"></i> My Bookings</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="btn-login">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['full_name'] ?? 'Dashboard'; ?>
                    </a></li>
                    <li><a href="logout.php" class="btn-register">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a></li>
                    <li><a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
    
    <div class="bookings-container">
        <h1 class="page-title"><i class="fas fa-calendar-alt"></i> My Bookings</h1>
        
        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times"></i>
                <h3>No bookings yet</h3>
                <p>You haven't booked any tour guides yet.</p>
                <a href="tour-guides.php" class="btn" style="display: inline-block; margin-top: 20px; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                    <i class="fas fa-search"></i> Find a Tour Guide
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">
                            #TG-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    
                    <div class="guide-info">
                        <div class="guide-image">
                            <img src="<?php echo htmlspecialchars($booking['profile_image'] ?? 'assets/images/default-guide.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($booking['guide_name']); ?>">
                        </div>
                        <div>
                            <h3><?php echo htmlspecialchars($booking['guide_name']); ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value">
                                <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value">
                                <?php echo date('g:i A', strtotime($booking['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($booking['end_time'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Duration</div>
                            <div class="detail-value"><?php echo $booking['hours']; ?> hours</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Group Size</div>
                            <div class="detail-value"><?php echo $booking['group_size']; ?> people</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Payment Status</div>
                            <div class="detail-value" style="color: <?php echo $booking['payment_status'] == 'paid' ? '#27ae60' : '#e74c3c'; ?>">
                                <?php echo ucfirst($booking['payment_status']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['special_requirements'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Special Requirements</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['special_requirements']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="total-amount">
                        $<?php echo number_format($booking['total_amount'], 2); ?>
                    </div>
                    
                    <div class="booking-actions">
                        <button class="btn btn-view" onclick="viewBookingDetails(<?php echo $booking['id']; ?>)">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        
                        <?php 
                            $bookingDate = new DateTime($booking['booking_date'] . ' ' . $booking['start_time']);
                            $now = new DateTime();
                            $isUpcoming = $bookingDate > $now;
                        ?>
                        
                        <?php if ($isUpcoming && $booking['status'] == 'pending'): ?>
                            <button class="btn btn-cancel" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                <i class="fas fa-times"></i> Cancel Booking
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function viewBookingDetails(bookingId) {
            window.location.href = `booking-details.php?id=${bookingId}`;
        }
        
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                fetch(`api/cancel-booking.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ booking_id: bookingId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking cancelled successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>