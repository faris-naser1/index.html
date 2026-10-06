<?php
// Check session status before starting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/config.php';

// Check if booking success data exists
if (!isset($_SESSION['booking_success'])) {
    // Store alert message
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'No booking data found. Please book a ticket first.'
    ];
    header('Location: index.php');
    exit;
}

$booking = $_SESSION['booking_success'];

// Store booking data for this page view
$booking_data = $booking;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - MatchTicketing</title>
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
            --light: #f8f9fa;
            --dark: #212529;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        /* ===== Success Container ===== */
        .success-container {
            max-width: 900px;
            margin: 100px auto 2rem;
            padding: 0 20px;
        }

        /* ===== Success Card ===== */
        .success-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            animation: cardAppear 0.8s ease-out;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Success Header ===== */
        .success-header {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 1%);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: bounce 1s infinite alternate;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        .success-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .success-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }

        .booking-ref {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            margin-top: 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* ===== Ticket Details ===== */
        .ticket-details {
            padding: 3rem 2.5rem;
        }

        .detail-section {
            background: var(--light);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 5px solid var(--primary);
            transition: transform 0.3s;
        }

        .detail-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.6rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .detail-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .detail-value {
            color: var(--dark);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .highlight {
            color: var(--accent);
            font-size: 1.3rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #d4edda;
            color: #155724;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ===== Countdown Timer ===== */
        .countdown-container {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            margin: 2rem 0;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .countdown-timer {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0.5rem 0;
            font-family: 'Courier New', monospace;
        }

        .timer-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* ===== Action Buttons ===== */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 3rem 0;
        }

        .btn-custom {
            padding: 1.2rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .btn-primary-custom {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(to right, var(--success), #20c997);
            color: white;
        }

        .btn-accent {
            background: linear-gradient(to right, var(--accent), #ff8b35);
            color: white;
        }

        /* ===== Redirect Message ===== */
        .redirect-message {
            text-align: center;
            padding: 2rem;
            background: var(--light);
            border-radius: 15px;
            margin-top: 2rem;
            border: 2px dashed #dee2e6;
        }

        .redirect-message a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 2px;
            transition: all 0.3s;
        }

        .redirect-message a:hover {
            color: var(--secondary);
            border-color: var(--secondary);
        }

        /* ===== Footer ===== */
        .page-footer {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 3rem;
            border-top: 1px solid #dee2e6;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 768px) {
            .success-container {
                margin: 80px auto 1rem;
                padding: 0 15px;
            }
            
            .success-header {
                padding: 2rem 1rem;
            }
            
            .success-header h1 {
                font-size: 2.2rem;
            }
            
            .success-icon {
                font-size: 4rem;
            }
            
            .ticket-details {
                padding: 2rem 1.5rem;
            }
            
            .detail-section {
                padding: 1.5rem;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .btn-custom {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
            
            .countdown-timer {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .success-header h1 {
                font-size: 1.8rem;
            }
            
            .success-header p {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-ticket-alt"></i>
                <span>MatchTicketing</span>
            </a>
        </div>
    </nav>

    <!-- Success Content -->
    <div class="success-container">
        <div class="success-card">
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Booking Confirmed!</h1>
                <p>Your tickets have been successfully booked. A confirmation email has been sent to your registered email address.</p>
                <div class="booking-ref">
                    <i class="fas fa-receipt"></i>
                    Booking Reference: #<?php echo $booking_data['ticket_id']; ?>
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="ticket-details">
                <!-- Match Details Section -->
                <div class="detail-section">
                    <h3 class="section-title">
                        <i class="fas fa-ticket-alt"></i>
                        Match Details
                    </h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-futbol"></i> MATCH
                            </div>
                            <div class="detail-value highlight">
                                <?php echo htmlspecialchars($booking_data['match_name']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-calendar"></i> DATE
                            </div>
                            <div class="detail-value">
                                <?php echo date('F j, Y', strtotime($booking_data['match_date'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-map-marker-alt"></i> VENUE
                            </div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($booking_data['venue']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-chair"></i> SEAT CATEGORY
                            </div>
                            <div class="detail-value">
                                <?php echo ucfirst($booking_data['seat_category']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Section -->
                <div class="detail-section">
                    <h3 class="section-title">
                        <i class="fas fa-receipt"></i>
                        Booking Summary
                    </h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-users"></i> TICKETS
                            </div>
                            <div class="detail-value highlight">
                                <?php echo $booking_data['quantity']; ?> Ticket<?php echo $booking_data['quantity'] > 1 ? 's' : ''; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-credit-card"></i> TOTAL AMOUNT
                            </div>
                            <div class="detail-value highlight">
                                $<?php echo number_format($booking_data['total_amount'], 2); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-wallet"></i> PAYMENT METHOD
                            </div>
                            <div class="detail-value">
                                <?php echo ucwords(str_replace('_', ' ', $booking_data['payment_method'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-hashtag"></i> TRANSACTION ID
                            </div>
                            <div class="detail-value">
                                <?php echo $booking_data['transaction_id']; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Section -->
                <div class="detail-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Customer Details
                    </h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-user"></i> NAME
                            </div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($booking_data['customer_name']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-envelope"></i> EMAIL
                            </div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($booking_data['customer_email']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-phone"></i> PHONE
                            </div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($booking_data['customer_phone']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-shield-alt"></i> STATUS
                            </div>
                            <div class="status-badge">
                                <i class="fas fa-check-circle"></i> CONFIRMED
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="countdown-container">
                    <div class="timer-label">
                        <i class="fas fa-clock"></i> Auto-redirect to tickets page in
                    </div>
                    <div class="countdown-timer" id="countdown">10</div>
                    <div class="timer-label">
                        You'll be redirected automatically to browse more tickets
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="my-bookings.php" class="btn-custom btn-primary-custom">
                        <i class="fas fa-ticket-alt"></i>
                        View My Bookings
                    </a>
                    <a href="index.php#tickets" class="btn-custom btn-success-custom">
                        <i class="fas fa-tags"></i>
                        Browse More Tickets
                    </a>
                    <button onclick="window.print()" class="btn-custom btn-accent">
                        <i class="fas fa-print"></i>
                        Print Ticket
                    </button>
                </div>

                <!-- Redirect Message -->
                <div class="redirect-message">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        You will be redirected to the tickets page in <strong id="redirectCountdown">10</strong> seconds.
                        <br>
                        <a href="index.php#tickets">
                            <i class="fas fa-arrow-right"></i> Click here to go now
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Page Footer -->
        <div class="page-footer">
            <p>
                <i class="fas fa-question-circle"></i>
                Need help? Contact our support team at support@matchticketing.com
            </p>
            <p class="mt-2">
                &copy; <?php echo date('Y'); ?> MatchTicketing. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer for auto-redirect
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        const redirectCountdownElement = document.getElementById('redirectCountdown');
        
        function updateCountdown() {
            if (seconds > 0) {
                seconds--;
                if (countdownElement) countdownElement.textContent = seconds;
                if (redirectCountdownElement) redirectCountdownElement.textContent = seconds;
                
                // Update countdown color based on remaining time
                if (seconds <= 3) {
                    countdownElement.style.color = '#ff6b35';
                } else if (seconds <= 7) {
                    countdownElement.style.color = '#ffc107';
                }
                
                setTimeout(updateCountdown, 1000);
            } else {
                // Redirect to tickets page
                window.location.href = 'index.php#tickets';
            }
        }
        
        // Start countdown
        document.addEventListener('DOMContentLoaded', function() {
            // Start countdown after 1 second delay
            setTimeout(updateCountdown, 1000);
            
            // Add print styles
            const printStyle = document.createElement('style');
            printStyle.textContent = `
                @media print {
                    .navbar, .countdown-container, .action-buttons, .redirect-message, .page-footer { 
                        display: none !important; 
                    }
                    body { 
                        background: white !important; 
                        padding: 20px;
                    }
                    .success-card { 
                        box-shadow: none !important; 
                        border: 2px solid #ddd !important; 
                        margin: 0 !important;
                    }
                    .success-header { 
                        background: #f8f9fa !important; 
                        color: black !important; 
                        border-bottom: 3px solid #28a745 !important;
                    }
                    .success-icon { 
                        color: #28a745 !important; 
                        animation: none !important;
                    }
                    .detail-section { 
                        page-break-inside: avoid; 
                        border: 1px solid #dee2e6 !important;
                    }
                    .btn-custom { display: none !important; }
                }
            `;
            document.head.appendChild(printStyle);
        });
        
        // Stop countdown if user interacts with page
        document.addEventListener('click', function() {
            seconds = 30; // Reset to 30 seconds when user clicks
        });
        
        document.addEventListener('keypress', function() {
            seconds = 30; // Reset to 30 seconds when user types
        });
        
        // Prevent accidental page close
        window.addEventListener('beforeunload', function(e) {
            if (seconds > 0) {
                // Only show confirmation if countdown is still running
                e.preventDefault();
                e.returnValue = 'Your booking confirmation is still displayed. Are you sure you want to leave?';
            }
        });
    </script>
</body>
</html>
<?php
// Clear booking success data from session (but keep a copy for potential refresh)
if (!isset($_SESSION['booking_success_backup'])) {
    $_SESSION['booking_success_backup'] = $_SESSION['booking_success'];
}
unset($_SESSION['booking_success']);

// Also clear any alert messages
if (isset($_SESSION['alert'])) {
    unset($_SESSION['alert']);
}
?>