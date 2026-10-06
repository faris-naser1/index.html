<?php
// dashboard.php - User Dashboard
require_once 'config.php';

// Check if user is logged in
redirectIfNotLoggedIn();

// Set page title
$page_title = "My Dashboard - MatchTicketing";

// Get user info
function getUserInfo($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching user info: " . $e->getMessage());
        return null;
    }
}

// Get all transport bookings for user
function getUserTransportBookings($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT tb.*, 
                   t.vehicle_name,
                   t.registration_no
            FROM transport_bookings tb
            LEFT JOIN transport t ON tb.vehicle_id = t.id
            WHERE tb.user_id = ? 
            ORDER BY tb.departure_date DESC, tb.booking_date DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching transport bookings: " . $e->getMessage());
        return [];
    }
}

// Get all hotel bookings for user
function getUserHotelBookings($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT hb.*,
                   h.name as hotel_name,
                   h.location,
                   h.image
            FROM hotel_bookings hb
            LEFT JOIN hotels h ON hb.hotel_id = h.id
            WHERE hb.user_id = ? 
            ORDER BY hb.check_in DESC, hb.booking_date DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching hotel bookings: " . $e->getMessage());
        return [];
    }
}

// Get all match ticket bookings for user
function getUserTicketBookings($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT tb.*,
                   m.match_name,
                   m.team1,
                   m.team2,
                   m.match_date,
                   m.match_time,
                   m.venue,
                   m.sport_type
            FROM ticket_bookings tb
            LEFT JOIN matches m ON tb.match_id = m.id
            WHERE tb.user_id = ? 
            ORDER BY m.match_date DESC, tb.booking_date DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching ticket bookings: " . $e->getMessage());
        return [];
    }
}

// Cancel transport booking
if (isset($_GET['cancel_transport']) && is_numeric($_GET['cancel_transport'])) {
    $booking_id = intval($_GET['cancel_transport']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT vehicle_id FROM transport_bookings 
                               WHERE id = ? AND user_id = ? AND status = 'confirmed'");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $pdo->beginTransaction();
            
            try {
                // Update booking status
                $update_stmt = $pdo->prepare("UPDATE transport_bookings 
                                             SET status = 'cancelled' 
                                             WHERE id = ? AND user_id = ?");
                $update_stmt->execute([$booking_id, $user_id]);
                
                // Free up the vehicle
                if ($booking['vehicle_id']) {
                    $vehicle_stmt = $pdo->prepare("UPDATE transport 
                                                  SET status = 'available' 
                                                  WHERE id = ?");
                    $vehicle_stmt->execute([$booking['vehicle_id']]);
                }
                
                $pdo->commit();
                setAlert('success', "Transport booking cancelled successfully!");
            } catch (Exception $e) {
                $pdo->rollBack();
                setAlert('error', "Error cancelling transport booking: " . $e->getMessage());
            }
        } else {
            setAlert('error', "Booking not found or already cancelled");
        }
    } catch (Exception $e) {
        error_log("Error cancelling transport booking: " . $e->getMessage());
        setAlert('error', "An error occurred while cancelling the booking");
    }
    
    header("Location: dashboard.php");
    exit();
}

// Cancel hotel booking
if (isset($_GET['cancel_hotel']) && is_numeric($_GET['cancel_hotel'])) {
    $booking_id = intval($_GET['cancel_hotel']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT hotel_id, guests FROM hotel_bookings 
                               WHERE id = ? AND user_id = ? AND status = 'confirmed'");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $pdo->beginTransaction();
            
            try {
                // Update booking status
                $update_stmt = $pdo->prepare("UPDATE hotel_bookings 
                                             SET status = 'cancelled' 
                                             WHERE id = ? AND user_id = ?");
                $update_stmt->execute([$booking_id, $user_id]);
                
                // Add rooms back to available rooms
                if ($booking['hotel_id']) {
                    $hotel_stmt = $pdo->prepare("UPDATE hotels 
                                                SET available_rooms = available_rooms + 1 
                                                WHERE id = ?");
                    $hotel_stmt->execute([$booking['hotel_id']]);
                }
                
                $pdo->commit();
                setAlert('success', "Hotel booking cancelled successfully!");
            } catch (Exception $e) {
                $pdo->rollBack();
                setAlert('error', "Error cancelling hotel booking: " . $e->getMessage());
            }
        } else {
            setAlert('error', "Booking not found or already cancelled");
        }
    } catch (Exception $e) {
        error_log("Error cancelling hotel booking: " . $e->getMessage());
        setAlert('error', "An error occurred while cancelling the booking");
    }
    
    header("Location: dashboard.php");
    exit();
}

// Cancel ticket booking
if (isset($_GET['cancel_ticket']) && is_numeric($_GET['cancel_ticket'])) {
    $booking_id = intval($_GET['cancel_ticket']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT match_id, quantity FROM ticket_bookings 
                               WHERE id = ? AND user_id = ? AND status = 'confirmed'");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $pdo->beginTransaction();
            
            try {
                // Update booking status
                $update_stmt = $pdo->prepare("UPDATE ticket_bookings 
                                             SET status = 'cancelled' 
                                             WHERE id = ? AND user_id = ?");
                $update_stmt->execute([$booking_id, $user_id]);
                
                $pdo->commit();
                setAlert('success', "Ticket booking cancelled successfully!");
            } catch (Exception $e) {
                $pdo->rollBack();
                setAlert('error', "Error cancelling ticket booking: " . $e->getMessage());
            }
        } else {
            setAlert('error', "Booking not found or already cancelled");
        }
    } catch (Exception $e) {
        error_log("Error cancelling ticket booking: " . $e->getMessage());
        setAlert('error', "An error occurred while cancelling the booking");
    }
    
    header("Location: dashboard.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_info = getUserInfo($user_id);
$transport_bookings = getUserTransportBookings($user_id);
$hotel_bookings = getUserHotelBookings($user_id);
$ticket_bookings = getUserTicketBookings($user_id);

// Calculate stats
$total_transport = count($transport_bookings);
$total_hotels = count($hotel_bookings);
$total_tickets = count($ticket_bookings);
$total_bookings = $total_transport + $total_hotels + $total_tickets;

// Calculate total spending
$total_spent = 0;
foreach ($transport_bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $total_spent += $booking['total_amount'];
    }
}
foreach ($hotel_bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $total_spent += $booking['total_amount'];
    }
}
foreach ($ticket_bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $total_spent += $booking['total_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #283593;
            --accent: #ff6b35;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }

        /* ===== Navigation ===== */
        .navbar {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo i {
            color: var(--accent);
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .nav-links a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .nav-links a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .btn-login, .btn-register {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-register {
            background: var(--accent);
            border: 2px solid var(--accent);
            color: white;
        }

        .btn-login:hover, .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .mobile-menu {
            display: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
        }

        /* ===== Page Header ===== */
        .page-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0 4rem;
            margin-top: 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(26,35,126,0.3), rgba(255,107,53,0.3));
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .page-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .page-header p {
            font-size: 1.3rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ===== Alert Messages ===== */
        .alert-container {
            max-width: 1200px;
            margin: 100px auto 1rem;
            padding: 0 20px;
        }

        .alert {
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 5px solid #28a745;
            color: #155724;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 5px solid #dc3545;
            color: #721c24;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ===== Main Content ===== */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ===== User Profile Card ===== */
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .profile-info h2 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        /* ===== Statistics Cards ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-card.transport {
            border-left: 5px solid var(--primary);
        }

        .stat-card.transport i {
            color: var(--primary);
        }

        .stat-card.hotel {
            border-left: 5px solid var(--accent);
        }

        .stat-card.hotel i {
            color: var(--accent);
        }

        .stat-card.ticket {
            border-left: 5px solid var(--success);
        }

        .stat-card.ticket i {
            color: var(--success);
        }

        .stat-card.total {
            border-left: 5px solid var(--warning);
        }

        .stat-card.total i {
            color: var(--warning);
        }

        .stat-number-lg {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            display: block;
            margin: 0.5rem 0;
        }

        /* ===== Booking Tabs ===== */
        .booking-tabs {
            margin: 2rem 0;
        }

        .tab-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .tab-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ===== Booking Cards ===== */
        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }

        .booking-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-type {
            font-size: 0.9rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .booking-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-confirmed {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .booking-body {
            padding: 1.5rem;
        }

        .booking-details {
            margin-bottom: 1rem;
        }

        .booking-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.8rem;
            color: #555;
        }

        .booking-detail i {
            color: var(--primary);
            width: 20px;
        }

        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex: 1;
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-cancel {
            background: var(--danger);
            color: white;
        }

        .btn-receipt {
            background: #6c757d;
            color: white;
        }

        .btn-view:hover, .btn-cancel:hover, .btn-receipt:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .booking-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 2rem;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 768px) {
            .profile-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .booking-grid {
                grid-template-columns: 1fr;
            }
            
            .tab-header {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
                justify-content: center;
            }
            
            .page-header h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .profile-stats {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- ===== Navigation ===== -->
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-user"></i> Dashboard</a></li>
                <li><a href="logout.php" class="btn-register">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
            </ul>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- ===== Alert Messages ===== -->
    <div class="alert-container">
        <?php $alert = getAlert(); ?>
        <?php if ($alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?>">
                <i class="fas fa-<?php echo $alert['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== Page Header ===== -->
    <section class="page-header">
        <div class="container">
            <h1>My Dashboard</h1>
            <p>Manage all your bookings in one place</p>
        </div>
    </section>

    <!-- ===== Main Content ===== -->
    <div class="dashboard-container">
        <!-- User Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user_info['full_name'] ?? $user_info['username']); ?></h2>
                <p class="text-muted">
                    <i class="fas fa-envelope me-2"></i>
                    <?php echo htmlspecialchars($user_info['email']); ?>
                </p>
                <?php if ($user_info['phone']): ?>
                <p class="text-muted">
                    <i class="fas fa-phone me-2"></i>
                    <?php echo htmlspecialchars($user_info['phone']); ?>
                </p>
                <?php endif; ?>
                <p class="text-muted">
                    <i class="fas fa-calendar me-2"></i>
                    Member since <?php echo date('F Y', strtotime($user_info['created_at'])); ?>
                </p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_bookings; ?></span>
                        <span class="stat-label">Total Bookings</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">$<?php echo number_format($total_spent, 2); ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($transport_bookings, fn($b) => $b['status'] === 'confirmed')); ?></span>
                        <span class="stat-label">Active Trips</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($hotel_bookings, fn($b) => $b['status'] === 'confirmed')); ?></span>
                        <span class="stat-label">Active Stays</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card transport">
                <i class="fas fa-bus"></i>
                <span class="stat-number-lg"><?php echo $total_transport; ?></span>
                <h3>Transport Bookings</h3>
                <p class="text-muted">Your upcoming and past trips</p>
            </div>
            
            <div class="stat-card hotel">
                <i class="fas fa-hotel"></i>
                <span class="stat-number-lg"><?php echo $total_hotels; ?></span>
                <h3>Hotel Bookings</h3>
                <p class="text-muted">Your hotel reservations</p>
            </div>
            
            <div class="stat-card ticket">
                <i class="fas fa-ticket-alt"></i>
                <span class="stat-number-lg"><?php echo $total_tickets; ?></span>
                <h3>Match Tickets</h3>
                <p class="text-muted">Your match ticket bookings</p>
            </div>
            
            <div class="stat-card total">
                <i class="fas fa-dollar-sign"></i>
                <span class="stat-number-lg">$<?php echo number_format($total_spent, 2); ?></span>
                <h3>Total Spent</h3>
                <p class="text-muted">All booking payments</p>
            </div>
        </div>

        <!-- Booking Tabs -->
        <div class="booking-tabs">
            <div class="tab-header">
                <button class="tab-btn active" data-tab="transport">
                    <i class="fas fa-bus"></i> Transport
                    <span class="badge bg-primary ms-2"><?php echo $total_transport; ?></span>
                </button>
                <button class="tab-btn" data-tab="hotels">
                    <i class="fas fa-hotel"></i> Hotels
                    <span class="badge bg-accent ms-2"><?php echo $total_hotels; ?></span>
                </button>
                <button class="tab-btn" data-tab="tickets">
                    <i class="fas fa-ticket-alt"></i> Match Tickets
                    <span class="badge bg-success ms-2"><?php echo $total_tickets; ?></span>
                </button>
            </div>

            <!-- Transport Bookings Tab -->
            <div id="transport-tab" class="tab-content active">
                <?php if (empty($transport_bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bus"></i>
                        <h3>No Transport Bookings</h3>
                        <p>You haven't booked any transport yet. Book your first trip!</p>
                        <a href="transport.php" class="btn btn-primary">
                            <i class="fas fa-bus me-2"></i> Book Transport
                        </a>
                    </div>
                <?php else: ?>
                    <div class="booking-grid">
                        <?php foreach ($transport_bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-type">
                          <i class="fas fa-<?php echo match ($booking['transport_type']) {
    'metro'      => 'subway',
    'bus'        => 'bus',
    'train'      => 'train',
    'car_rental' => 'car',
    'taxi'       => 'taxi',
    'shuttle'    => 'shuttle-van',
    default      => 'bus',
}; ?>"></i>

                                    <span><?php echo ucfirst(str_replace('_', ' ', $booking['transport_type'])); ?></span>
                                </div>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-body">
                                <div class="booking-details">
                                    <div class="booking-detail">
                                        <i class="fas fa-route"></i>
                                        <span><?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo date('g:i A', strtotime($booking['departure_time'])); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $booking['quantity']; ?> passenger(s)</span>
                                    </div>
                                    <?php if ($booking['vehicle_name']): ?>
                                    <div class="booking-detail">
                                        <i class="fas fa-bus-alt"></i>
                                        <span><?php echo htmlspecialchars($booking['vehicle_name']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-total">
                                    $<?php echo number_format($booking['total_amount'], 2); ?>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <a href="dashboard.php?cancel_transport=<?php echo $booking['id']; ?>" 
                                           class="btn-small btn-cancel"
                                           onclick="return confirm('Are you sure you want to cancel this transport booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    <a href="receipt.php?id=<?php echo $booking['id']; ?>&type=transport" 
                                       target="_blank" class="btn-small btn-receipt">
                                        <i class="fas fa-receipt"></i> Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hotel Bookings Tab -->
            <div id="hotels-tab" class="tab-content">
                <?php if (empty($hotel_bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-hotel"></i>
                        <h3>No Hotel Bookings</h3>
                        <p>You haven't booked any hotels yet. Find your perfect stay!</p>
                        <a href="hotels.php" class="btn btn-primary">
                            <i class="fas fa-hotel me-2"></i> Book Hotel
                        </a>
                    </div>
                <?php else: ?>
                    <div class="booking-grid">
                        <?php foreach ($hotel_bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-type">
                                    <i class="fas fa-hotel"></i>
                                    <span>Hotel Booking</span>
                                </div>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-body">
                                <div class="booking-details">
                                    <div class="booking-detail">
                                        <i class="fas fa-building"></i>
                                        <span><?php echo htmlspecialchars($booking['hotel_name']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($booking['location']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M d, Y', strtotime($booking['check_in'])); ?> - <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $booking['guests']; ?> guest(s), <?php echo $booking['nights']; ?> night(s)</span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo htmlspecialchars($booking['room_type']); ?> Room</span>
                                    </div>
                                </div>
                                <div class="booking-total">
                                    $<?php echo number_format($booking['total_amount'], 2); ?>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <a href="dashboard.php?cancel_hotel=<?php echo $booking['id']; ?>" 
                                           class="btn-small btn-cancel"
                                           onclick="return confirm('Are you sure you want to cancel this hotel booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    <a href="hotel_receipt.php?id=<?php echo $booking['id']; ?>" 
                                       target="_blank" class="btn-small btn-receipt">
                                        <i class="fas fa-receipt"></i> Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ticket Bookings Tab -->
            <div id="tickets-tab" class="tab-content">
                <?php if (empty($ticket_bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>No Ticket Bookings</h3>
                        <p>You haven't booked any match tickets yet. Catch the action live!</p>
                        <a href="tickets.php" class="btn btn-primary">
                            <i class="fas fa-ticket-alt me-2"></i> Buy Tickets
                        </a>
                    </div>
                <?php else: ?>
                    <div class="booking-grid">
                        <?php foreach ($ticket_bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-type">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>Match Ticket</span>
                                </div>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-body">
                                <div class="booking-details">
                                    <div class="booking-detail">
                                        <i class="fas fa-futbol"></i>
                                        <span><?php echo htmlspecialchars($booking['match_name']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-flag"></i>
                                        <span><?php echo htmlspecialchars($booking['team1']); ?> vs <?php echo htmlspecialchars($booking['team2']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M d, Y', strtotime($booking['match_date'])); ?> at <?php echo date('g:i A', strtotime($booking['match_time'])); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($booking['venue']); ?></span>
                                    </div>
                                    <div class="booking-detail">
                                        <i class="fas fa-ticket"></i>
                                        <span><?php echo $booking['quantity']; ?> ticket(s), <?php echo htmlspecialchars($booking['ticket_type']); ?></span>
                                    </div>
                                </div>
                                <div class="booking-total">
                                    $<?php echo number_format($booking['total_amount'], 2); ?>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <a href="dashboard.php?cancel_ticket=<?php echo $booking['id']; ?>" 
                                           class="btn-small btn-cancel"
                                           onclick="return confirm('Are you sure you want to cancel this ticket booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    <a href="ticket_receipt.php?id=<?php echo $booking['id']; ?>" 
                                       target="_blank" class="btn-small btn-receipt">
                                        <i class="fas fa-receipt"></i> Receipt
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenu = document.querySelector('.mobile-menu');
            const navLinks = document.querySelector('.nav-links');
            
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                });
            }
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar')) {
                    navLinks.style.display = 'none';
                }
            });
            
            // Tab switching functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to current tab
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>