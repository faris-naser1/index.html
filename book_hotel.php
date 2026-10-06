<?php
// book_hotel.php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get hotel_id from URL if coming from hotels page
$selected_hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;

// Fetch hotel details
$hotel = null;
if ($selected_hotel_id > 0) {
    $hotel = getHotelById($selected_hotel_id);
}

$success = false;
$booking_data = null;
$alert = getAlert();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
    $room_type = $_POST['room_type'] ?? 'single';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    // Fetch hotel details
    $hotel = getHotelById($hotel_id);
    
    if (!$hotel) {
        setAlert('error', 'Hotel not found');
        header('Location: book_hotel.php');
        exit;
    }
    
    // Validate dates
    if (empty($check_in) || empty($check_out)) {
        setAlert('error', 'Please select check-in and check-out dates.');
        header('Location: book_hotel.php?hotel_id=' . $hotel_id);
        exit;
    }
    
    if (strtotime($check_out) <= strtotime($check_in)) {
        setAlert('error', 'Check-out date must be after check-in date.');
        header('Location: book_hotel.php?hotel_id=' . $hotel_id);
        exit;
    }
    
    // Calculate nights
    $nights = ceil((strtotime($check_out) - strtotime($check_in)) / (86400));
    
    if ($nights <= 0 || $nights > 30) {
        setAlert('error', 'Invalid number of nights. Maximum stay is 30 nights.');
        header('Location: book_hotel.php?hotel_id=' . $hotel_id);
        exit;
    }
    
    // Validate guests
    if ($guests < 1 || $guests > 6) {
        setAlert('error', 'Number of guests must be between 1 and 6.');
        header('Location: book_hotel.php?hotel_id=' . $hotel_id);
        exit;
    }
    
    // Calculate price based on room type
    $room_prices = [
        'single' => $hotel['price_per_night'],
        'double' => $hotel['price_per_night'] * 1.2,
        'suite' => $hotel['price_per_night'] * 1.5,
        'deluxe' => $hotel['price_per_night'] * 2,
        'family' => $hotel['price_per_night'] * 1.8,
        'executive' => $hotel['price_per_night'] * 2.5
    ];
    
    $price_per_night = $room_prices[$room_type] ?? $hotel['price_per_night'];
    $total_amount = $price_per_night * $nights;
    
    try {
        // Process payment
        $payment = processPayment($total_amount, $payment_method);
        
        if ($payment['success']) {
            // Check if hotel has enough rooms
            if ($hotel['available_rooms'] <= 0) {
                setAlert('error', 'Sorry, this hotel is fully booked.');
                header('Location: book_hotel.php?hotel_id=' . $hotel_id);
                exit;
            }
            
            // Prepare booking data
            $booking_data = [
                'user_id' => $user_id,
                'hotel_id' => $hotel_id,
                'hotel_name' => $hotel['name'],
                'location' => $hotel['location'],
                'room_type' => $room_type,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'guests' => $guests,
                'nights' => $nights,
                'price_per_night' => $price_per_night,
                'total_amount' => $total_amount,
                'payment_method' => $payment_method,
                'transaction_id' => $payment['transaction_id']
            ];
            
            // Create booking using function from config.php
            $booking_result = createHotelBooking($booking_data);
            
            if ($booking_result) {
                // Update available rooms
                updateHotelRooms($hotel_id, -1);
                
                // Get the booking ID
                $booking_id = $pdo->lastInsertId();
                
                // Store booking data for display
                $booking_data['booking_id'] = $booking_id;
                $booking_data['booking_date'] = date('Y-m-d H:i:s');
                
                $success = true;
                setAlert('success', 'Hotel booked successfully!');
            } else {
                setAlert('error', 'Failed to create booking. Please try again.');
            }
        } else {
            setAlert('error', 'Payment failed. Please try again.');
        }
        
    } catch (Exception $e) {
        setAlert('error', 'Error: ' . $e->getMessage());
    }
    
    // Refresh page to show alert
    header('Location: book_hotel.php?hotel_id=' . $hotel_id);
    exit;
}

// If no hotel selected, redirect to hotels page
if (!$hotel && !$success) {
    setAlert('error', 'Please select a hotel to book.');
    header('Location: hotels.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Hotel - MatchTicketing</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #283593;
            --accent: #ff6b35;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 80px auto 2rem;
            padding: 0 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 3px solid var(--accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
        }
        
        .btn-booking {
            background: linear-gradient(to right, var(--accent), #ff8b35);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-booking:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,53,0.3);
        }
        
        .booking-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
            background: rgba(26,35,126,0.05);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: rgba(26,35,126,0.1);
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            border-top: 5px solid #28a745;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .booking-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .room-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .room-option:hover {
            border-color: var(--primary);
            background: rgba(26,35,126,0.05);
        }
        
        .room-option.selected {
            border-color: var(--primary);
            background: rgba(26,35,126,0.1);
        }
        
        .hotel-info-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .alert-container {
            margin-top: 80px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-ticket-alt"></i> MatchTicketing
            </a>
            <div class="d-flex">
                <a href="hotels.php" class="back-btn me-2">
                    <i class="fas fa-arrow-left"></i> Back to Hotels
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline-light">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="alert-container">
        <?php if($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $alert['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $alert['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    </div>

    <div class="booking-container">
        <?php if($success && $booking_data): ?>
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check fa-2x text-white"></i>
            </div>
            <h2 class="text-success mb-3">Booking Successful!</h2>
            <p class="lead mb-4">Your hotel has been booked and payment processed successfully.</p>
            
            <!-- Booking Details -->
            <div class="booking-details text-start">
                <h4 class="mb-3">Booking Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking ID:</strong> <?php echo $booking_data['booking_id']; ?></p>
                        <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking_data['hotel_name']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($booking_data['location']); ?></p>
                        <p><strong>Check-in:</strong> <?php echo date('F j, Y', strtotime($booking_data['check_in'])); ?></p>
                        <p><strong>Check-out:</strong> <?php echo date('F j, Y', strtotime($booking_data['check_out'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Room Type:</strong> <?php echo ucfirst($booking_data['room_type']); ?></p>
                        <p><strong>Nights:</strong> <?php echo $booking_data['nights']; ?></p>
                        <p><strong>Guests:</strong> <?php echo $booking_data['guests']; ?></p>
                        <p><strong>Price per Night:</strong> $<?php echo number_format($booking_data['price_per_night'], 2); ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($booking_data['total_amount'], 2); ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> <?php echo ucfirst($booking_data['payment_method']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Transaction ID:</strong> <?php echo $booking_data['transaction_id']; ?></p>
                    </div>
                </div>
                <p class="text-muted mt-3"><small>Booking Date: <?php echo $booking_data['booking_date']; ?></small></p>
            </div>
            
            <div class="mt-4">
                <a href="hotels.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-hotel"></i> Book Another Hotel
                </a>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-user"></i> View in Dashboard
                </a>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- Booking Form -->
        <div class="booking-card">
            <h2 class="section-title">Book Your Hotel</h2>
            
            <!-- Hotel Info -->
            <div class="hotel-info-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                            <?php if(isset($hotel['distance_to_stadium'])): ?>
                            <span class="ms-3"><i class="fas fa-walking"></i> <?php echo $hotel['distance_to_stadium']; ?> to stadium</span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-0"><?php echo htmlspecialchars($hotel['description']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="display-6 fw-bold text-primary">$<?php echo number_format($hotel['price_per_night'], 0); ?></div>
                        <div class="text-muted">per night</div>
                        <div class="mt-2">
                            <span class="badge bg-primary">
                                <i class="fas fa-star"></i> <?php echo number_format($hotel['rating'], 1); ?>
                            </span>
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-bed"></i> <?php echo $hotel['available_rooms']; ?> rooms left
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" id="bookingForm">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                
                <!-- Dates Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-calendar-check"></i> Check-in Date
                        </label>
                        <input type="date" class="form-control" name="check_in" id="check_in" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-calendar-times"></i> Check-out Date
                        </label>
                        <input type="date" class="form-control" name="check_out" id="check_out" required>
                    </div>
                </div>
                
                <!-- Room Type -->
                <div class="mb-4">
                    <label class="form-label">Select Room Type</label>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="single">
                                <div class="text-center">
                                    <i class="fas fa-bed fa-2x text-primary mb-2"></i>
                                    <h6>Single Room</h6>
                                    <p class="small text-muted">1 guest • Standard amenities</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'], 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="double">
                                <div class="text-center">
                                    <i class="fas fa-bed fa-2x text-warning mb-2"></i>
                                    <h6>Double Room</h6>
                                    <p class="small text-muted">2 guests • Queen bed</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'] * 1.2, 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="suite">
                                <div class="text-center">
                                    <i class="fas fa-crown fa-2x text-danger mb-2"></i>
                                    <h6>Suite</h6>
                                    <p class="small text-muted">Luxury • Living area</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'] * 1.5, 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="deluxe">
                                <div class="text-center">
                                    <i class="fas fa-star fa-2x text-success mb-2"></i>
                                    <h6>Deluxe Room</h6>
                                    <p class="small text-muted">Premium • Best view</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'] * 2, 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="family">
                                <div class="text-center">
                                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                                    <h6>Family Room</h6>
                                    <p class="small text-muted">4 guests • Extra space</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'] * 1.8, 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="room-option" data-room-type="executive">
                                <div class="text-center">
                                    <i class="fas fa-building fa-2x text-dark mb-2"></i>
                                    <h6>Executive Room</h6>
                                    <p class="small text-muted">Business • Extra services</p>
                                    <p class="fw-bold">$<?php echo number_format($hotel['price_per_night'] * 2.5, 0); ?> per night</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="room_type" id="room_type" value="single" required>
                </div>
                
                <!-- Guests -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-users"></i> Number of Guests
                        </label>
                        <select class="form-select" name="guests" id="guests" required>
                            <?php for($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> guest<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="form-label">Select Payment Method</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="payment-method" data-method="card">
                                <div class="text-center">
                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                    <h6>Credit/Debit Card</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method" data-method="paypal">
                                <div class="text-center">
                                    <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                    <h6>PayPal</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method" data-method="wallet">
                                <div class="text-center">
                                    <i class="fas fa-wallet fa-2x text-primary mb-2"></i>
                                    <h6>Wallet</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method" value="card" required>
                </div>
                
                <!-- Card Details -->
                <div id="cardDetails" class="mb-4">
                    <h5>Card Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>
                
                <!-- Price Preview -->
                <div class="booking-preview">
                    <h5>Booking Summary</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1">Hotel:</p>
                            <h6><?php echo htmlspecialchars($hotel['name']); ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Room Type:</p>
                            <h6 id="preview_room">Single Room</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Nights:</p>
                            <h6 id="preview_nights">0</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Total Amount:</p>
                            <h4 id="preview_total">$0.00</h4>
                        </div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="form-check mb-4 mt-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-booking btn-lg">
                        <i class="fas fa-lock"></i> Book Now & Pay
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hotel Booking Terms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>1. Free cancellation up to 48 hours before check-in.</p>
                    <p>2. Check-in: 3:00 PM | Check-out: 11:00 AM</p>
                    <p>3. Breakfast included for all room types.</p>
                    <p>4. Free Wi-Fi and parking available.</p>
                    <p>5. Shuttle service to stadium on match days.</p>
                    <p>6. Valid ID required at check-in.</p>
                    <p>7. Early check-in/late check-out subject to availability.</p>
                    <p>8. Payment will be charged immediately upon booking.</p>
                    <p>9. No refunds for no-shows or early check-outs.</p>
                    <p>10. Room rates are subject to change without notice.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            document.getElementById('check_in').min = today;
            document.getElementById('check_in').value = today;
            document.getElementById('check_out').min = tomorrowStr;
            document.getElementById('check_out').value = tomorrowStr;
            
            // Initialize booking preview
            updateBookingPreview();
            
            // Room type selection
            document.querySelectorAll('.room-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.room-option').forEach(el => {
                        el.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    
                    const roomType = this.dataset.roomType;
                    document.getElementById('room_type').value = roomType;
                    updateBookingPreview();
                });
            });
            
            // Set first room as selected by default
            document.querySelector('.room-option').classList.add('selected');
            
            // Payment method selection
            document.querySelectorAll('.payment-method').forEach(method => {
                method.addEventListener('click', function() {
                    document.querySelectorAll('.payment-method').forEach(el => {
                        el.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    
                    const paymentMethod = this.dataset.method;
                    document.getElementById('payment_method').value = paymentMethod;
                    
                    // Show/hide card details
                    const cardDetails = document.getElementById('cardDetails');
                    if (paymentMethod === 'card') {
                        cardDetails.style.display = 'block';
                    } else {
                        cardDetails.style.display = 'none';
                    }
                });
            });
            
            // Set first payment method as selected by default
            document.querySelector('.payment-method').classList.add('selected');
            
            // Date change listeners
            document.getElementById('check_in').addEventListener('change', function() {
                const checkIn = new Date(this.value);
                const checkOut = new Date(checkIn);
                checkOut.setDate(checkOut.getDate() + 1);
                
                const checkOutInput = document.getElementById('check_out');
                checkOutInput.min = checkOut.toISOString().split('T')[0];
                
                if (new Date(checkOutInput.value) < checkOut) {
                    checkOutInput.value = checkOut.toISOString().split('T')[0];
                }
                
                updateBookingPreview();
            });
            
            document.getElementById('check_out').addEventListener('change', updateBookingPreview);
            
            // Guests change listener
            document.getElementById('guests').addEventListener('change', updateBookingPreview);
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Update booking preview
        function updateBookingPreview() {
            const roomType = document.getElementById('room_type').value;
            const checkIn = new Date(document.getElementById('check_in').value);
            const checkOut = new Date(document.getElementById('check_out').value);
            
            // Calculate nights
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            // Room type names
            const roomNames = {
                'single': 'Single Room',
                'double': 'Double Room',
                'suite': 'Suite',
                'deluxe': 'Deluxe Room',
                'family': 'Family Room',
                'executive': 'Executive Room'
            };
            
            // Room price multipliers
            const roomMultipliers = {
                'single': 1.0,
                'double': 1.2,
                'suite': 1.5,
                'deluxe': 2.0,
                'family': 1.8,
                'executive': 2.5
            };
            
            // Base price from PHP
            const basePrice = <?php echo isset($hotel['price_per_night']) ? $hotel['price_per_night'] : 150; ?>;
            const roomMultiplier = roomMultipliers[roomType] || 1.0;
            const pricePerNight = basePrice * roomMultiplier;
            const totalAmount = pricePerNight * nights;
            
            // Update preview elements
            document.getElementById('preview_room').textContent = roomNames[roomType] || 'Single Room';
            document.getElementById('preview_nights').textContent = nights + ' night' + (nights !== 1 ? 's' : '');
            document.getElementById('preview_total').textContent = '$' + totalAmount.toFixed(2);
        }
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const checkIn = new Date(document.getElementById('check_in').value);
            const checkOut = new Date(document.getElementById('check_out').value);
            const guests = parseInt(document.getElementById('guests').value);
            
            // Validate check-out date
            if (checkOut <= checkIn) {
                e.preventDefault();
                alert('Check-out date must be after check-in date.');
                return;
            }
            
            // Validate maximum stay
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 3600 * 24));
            if (nights > 30) {
                e.preventDefault();
                alert('Maximum stay is 30 nights.');
                return;
            }
            
            // Validate guests
            if (guests < 1 || guests > 6) {
                e.preventDefault();
                alert('Number of guests must be between 1 and 6.');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Validate card details if payment method is card
            const paymentMethod = document.getElementById('payment_method').value;
            if (paymentMethod === 'card') {
                const cardNumber = this.querySelector('input[placeholder*="Card Number"]').value;
                const expiryDate = this.querySelector('input[placeholder*="MM/YY"]').value;
                const cvv = this.querySelector('input[placeholder*="CVV"]').value;
                
                if (!isValidCardNumber(cardNumber)) {
                    e.preventDefault();
                    alert('Please enter a valid 16-digit card number.');
                    submitBtn.innerHTML = '<i class="fas fa-lock"></i> Book Now & Pay';
                    submitBtn.disabled = false;
                    return;
                }
                
                if (!isValidExpiryDate(expiryDate)) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY).');
                    submitBtn.innerHTML = '<i class="fas fa-lock"></i> Book Now & Pay';
                    submitBtn.disabled = false;
                    return;
                }
                
                if (!isValidCVV(cvv)) {
                    e.preventDefault();
                    alert('Please enter a valid 3 or 4 digit CVV.');
                    submitBtn.innerHTML = '<i class="fas fa-lock"></i> Book Now & Pay';
                    submitBtn.disabled = false;
                    return;
                }
            }
        });
        
        // Validation helpers
        function isValidCardNumber(cardNumber) {
            cardNumber = cardNumber.replace(/\s/g, '');
            return /^\d{16}$/.test(cardNumber);
        }
        
        function isValidExpiryDate(expiryDate) {
            if (!/^\d{2}\/\d{2}$/.test(expiryDate)) return false;
            
            const [month, year] = expiryDate.split('/').map(num => parseInt(num, 10));
            const currentYear = new Date().getFullYear() % 100;
            const currentMonth = new Date().getMonth() + 1;
            
            if (month < 1 || month > 12) return false;
            if (year < currentYear) return false;
            if (year === currentYear && month < currentMonth) return false;
            
            return true;
        }
        
        function isValidCVV(cvv) {
            return /^\d{3,4}$/.test(cvv);
        }
        
        // Format card number input
        document.querySelectorAll('input[placeholder*="Card Number"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.substring(0, 16);
                value = value.replace(/(\d{4})/g, '$1 ').trim();
                e.target.value = value;
            });
        });
        
        // Format expiry date input
        document.querySelectorAll('input[placeholder*="MM/YY"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        });
        
        // Format CVV input
        document.querySelectorAll('input[placeholder*="CVV"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.substring(0, 4);
                e.target.value = value;
            });
        });
    </script>
</body>
</html>