<?php
// transport.php - Transportation Booking Page
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = "Transport Booking - MatchTicketing";

// Database functions with error handling
function getAvailableTransports() {
    global $conn;
    
    if (!$conn) return [];
    
    $query = "SELECT * FROM transport 
              WHERE status = 'available' 
              AND deleted_at IS NULL 
              ORDER BY created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) return [];
    
    $transports = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $transports[] = $row;
    }
    
    return $transports;
}

function getTransportLocations() {
    global $conn;
    
    if (!$conn) return ['Stadium', 'Airport', 'City Center', 'Hotel Zone', 'Train Station', 'Bus Terminal'];
    
    $query = "SELECT DISTINCT from_location as location FROM transport_bookings 
              UNION 
              SELECT DISTINCT to_location as location FROM transport_bookings 
              ORDER BY location";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) return ['Stadium', 'Airport', 'City Center'];
    
    $locations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row['location'];
    }
    
    return array_unique(array_filter($locations));
}

function getTransportTypes() {
    return ['Metro', 'Bus', 'Train', 'Car Rental', 'Taxi', 'Shuttle'];
}

function getUserTransportBookings() {
    global $conn;
    
    if (!$conn) return [];
    
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "SELECT * FROM transport_bookings 
              WHERE user_id = '$user_id' 
              ORDER BY departure_date DESC, departure_time DESC 
              LIMIT 5";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) return [];
    
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    
    return $bookings;
}

// Get data
$transports = getAvailableTransports();
$locations = getTransportLocations();
$transport_types = getTransportTypes();
$user_bookings = getUserTransportBookings();

$hasTransports = !empty($transports);

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_transport']) && $conn) {
    $transport_type = mysqli_real_escape_string($conn, $_POST['transport_type']);
    $departure_date = mysqli_real_escape_string($conn, $_POST['departure_date']);
    $departure_time = mysqli_real_escape_string($conn, $_POST['departure_time']);
    $from_location = mysqli_real_escape_string($conn, $_POST['from_location']);
    $to_location = mysqli_real_escape_string($conn, $_POST['to_location']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $total_amount = floatval($_POST['total_amount']);
    $user_id = $_SESSION['user_id'];
    
    // Calculate estimated arrival
    $estimated_hours = match($transport_type) {
        'metro' => 0.5,
        'bus' => 1,
        'train' => 2,
        'car_rental' => 3,
        'taxi' => 1,
        'shuttle' => 1.5,
        default => 1
    };
    
    $estimated_arrival = date('H:i', strtotime($departure_time . " +$estimated_hours hours"));
    
    $query = "INSERT INTO transport_bookings (
                user_id,
                transport_type,
                departure_date,
                departure_time,
                estimated_arrival,
                from_location,
                to_location,
                quantity,
                price_per_ticket,
                total_amount,
                status,
                booking_date
              ) VALUES (
                '$user_id',
                '$transport_type',
                '$departure_date',
                '$departure_time',
                '$estimated_arrival',
                '$from_location',
                '$to_location',
                $quantity,
                $price,
                $total_amount,
                'confirmed',
                NOW()
              )";
    
    if (mysqli_query($conn, $query)) {
        $booking_id = mysqli_insert_id($conn);
        $_SESSION['success_message'] = "Transport booked successfully! Booking ID: TRP" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
        header("Location: transport.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error booking transport: " . mysqli_error($conn);
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
       <link rel="stylesheet" href="css/tans-style.css">
    
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
                <li><a href="tickets.php" class="active"><i class="fas fa-ticket-alt"></i> Match Tickets</a></li>
                <li><a href="hotels.php"><i class="fas fa-hotel"></i> Hotels</a></li>
                <li><a href="transport.php"><i class="fas fa-bus"></i> Transport</a></li>
                 <li><a href="tour-guides.php"><i class="fas fa-bus"></i> Tour Guides</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
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

    <!-- ===== Flash Messages ===== -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-container">
            <div class="alert alert-success">
                <div>
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <button class="close-alert">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert-container">
            <div class="alert alert-error">
                <div>
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error_message']; ?>
                </div>
                <button class="close-alert">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- ===== Page Header ===== -->
    <section class="page-header">
        <div class="container">
            <h1>Book Your Transport</h1>
            <p>Convenient transportation to and from stadiums for the ultimate match experience</p>
        </div>
    </section>

    <!-- ===== Booking Section ===== -->
    <div class="booking-container">
        <div class="booking-wrapper">
            <!-- Booking Form -->
            <div class="booking-form-card">
                <h2 class="form-title">
                    <i class="fas fa-route"></i> Book Your Transport
                </h2>
                
                <form method="POST" id="transportBookingForm">
                    <!-- Transport Type -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-subway"></i> Transport Type
                        </label>
                        <div class="transport-options">
                            <label class="transport-option metro">
                                <input type="radio" name="transport_type" value="metro" required>
                                <i class="fas fa-subway"></i>
                                <span>Metro</span>
                                <div class="transport-price">$5-10 per ticket</div>
                            </label>
                            <label class="transport-option bus">
                                <input type="radio" name="transport_type" value="bus" required>
                                <i class="fas fa-bus"></i>
                                <span>Bus</span>
                                <div class="transport-price">$10-20 per ticket</div>
                            </label>
                            <label class="transport-option train">
                                <input type="radio" name="transport_type" value="train" required>
                                <i class="fas fa-train"></i>
                                <span>Train</span>
                                <div class="transport-price">$20-50 per ticket</div>
                            </label>
                            <label class="transport-option car_rental">
                                <input type="radio" name="transport_type" value="car_rental" required>
                                <i class="fas fa-car"></i>
                                <span>Car Rental</span>
                                <div class="transport-price">$50-100 per day</div>
                            </label>
                            <label class="transport-option taxi">
                                <input type="radio" name="transport_type" value="taxi" required>
                                <i class="fas fa-taxi"></i>
                                <span>Taxi</span>
                                <div class="transport-price">$20-40 per ride</div>
                            </label>
                            <label class="transport-option shuttle">
                                <input type="radio" name="transport_type" value="shuttle" required>
                                <i class="fas fa-shuttle-van"></i>
                                <span>Shuttle</span>
                                <div class="transport-price">$15-30 per ticket</div>
                            </label>
                        </div>
                    </div>

                    <!-- Date and Time -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="departure_date">
                                <i class="fas fa-calendar"></i> Travel Date
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="departure_date" 
                                   name="departure_date" 
                                   required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="departure_time">
                                <i class="fas fa-clock"></i> Departure Time
                            </label>
                            <input type="time" 
                                   class="form-control" 
                                   id="departure_time" 
                                   name="departure_time" 
                                   required>
                        </div>
                    </div>

                    <!-- Locations -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="from_location">
                                <i class="fas fa-map-marker-alt"></i> From
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="from_location" 
                                   name="from_location" 
                                   placeholder="Starting point (e.g., Stadium, Airport)" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="to_location">
                                <i class="fas fa-map-marker-alt"></i> To
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="to_location" 
                                   name="to_location" 
                                   placeholder="Destination (e.g., Hotel, City Center)" 
                                   required>
                        </div>
                    </div>

                    <!-- Passengers and Price -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="quantity">
                                <i class="fas fa-users"></i> Number of Passengers
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="quantity" 
                                   min="1" 
                                   max="10" 
                                   value="1" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="price">
                                <i class="fas fa-dollar-sign"></i> Price per Ticket ($)
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="price" 
                                   name="price" 
                                   min="1" 
                                   step="0.01" 
                                   value="10.00" 
                                   required>
                        </div>
                    </div>

                    <input type="hidden" name="total_amount" id="total_input">
                    <input type="hidden" name="book_transport" value="1">
                    
                    <button type="submit" class="book-btn" id="submitBtn">
                        <i class="fas fa-check-circle"></i> Book Transport Now
                    </button>
                </form>
            </div>

            <!-- Booking Summary -->
            <div class="booking-summary-card">
                <h2 class="summary-title">
                    <i class="fas fa-receipt"></i> Booking Summary
                </h2>
                
                <div class="summary-item">
                    <span>Transport Type:</span>
                    <span id="summary_type">-</span>
                </div>
                <div class="summary-item">
                    <span>Route:</span>
                    <span id="summary_route">-</span>
                </div>
                <div class="summary-item">
                    <span>Date & Time:</span>
                    <span id="summary_datetime">-</span>
                </div>
                <div class="summary-item">
                    <span>Passengers:</span>
                    <span id="summary_passengers">1</span>
                </div>
                <div class="summary-item total">
                    <span>Total Amount:</span>
                    <span id="total_amount">$10.00</span>
                </div>
                
                <div class="summary-details">
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span>Estimated travel time: <span id="estimated_time">-</span></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-route"></i>
                        <span>Distance: <span id="estimated_distance">-</span></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Price includes all taxes and fees</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Free cancellation up to 24 hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Recent Bookings ===== -->
    <div class="recent-bookings">
        <h2 class="section-title">Recent Transport Bookings</h2>
        
        <?php if (empty($user_bookings)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-bus"></i>
                </div>
                <h3>No Transport Bookings Yet</h3>
                <p>Your transport bookings will appear here once you make a booking. Book your first transport to get started!</p>
            </div>
        <?php else: ?>
            <!-- Bookings Grid -->
            <div class="bookings-grid">
                <?php foreach($user_bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">TRP<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
                        <span class="booking-status status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    <div class="booking-content">
                        <div class="booking-details">
                            <div class="booking-detail">
                                <i class="fas fa-subway"></i>
                                <span><?php echo ucfirst(str_replace('_', ' ', $booking['transport_type'])); ?></span>
                            </div>
                            <div class="booking-route">
                                <i class="fas fa-arrow-right"></i>
                                <span><?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $booking['departure_time']; ?> - <?php echo $booking['estimated_arrival']; ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-users"></i>
                                <span><?php echo $booking['quantity']; ?> passenger(s)</span>
                            </div>
                        </div>
                        
                        <div class="booking-total">
                            $<?php echo number_format($booking['total_amount'], 2); ?>
                        </div>
                        
                        <div class="booking-actions">
                            <?php if ($booking['status'] == 'confirmed'): ?>
                                <button class="btn-small btn-warning cancel-booking" 
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            <?php endif; ?>
                            <button class="btn-small btn-secondary view-receipt" 
                                    data-booking-id="<?php echo $booking['id']; ?>">
                                <i class="fas fa-receipt"></i> Receipt
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
     
        <?php endif; ?>
    </div>

    <!-- ===== Footer ===== -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MatchTicketing</h3>
                    <p>Your one-stop solution for sports events</p>
                    <p><i class="fas fa-envelope"></i> support@matchticketing.com</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                        <li><a href="hotels.php"><i class="fas fa-hotel"></i> Hotels</a></li>
                        <li><a href="transport.php"><i class="fas fa-bus"></i> Transport</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Transport Types</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-subway"></i> Metro</a></li>
                        <li><a href="#"><i class="fas fa-bus"></i> Bus</a></li>
                        <li><a href="#"><i class="fas fa-train"></i> Train</a></li>
                        <li><a href="#"><i class="fas fa-car"></i> Car Rental</a></li>
                        <li><a href="#"><i class="fas fa-shuttle-van"></i> Shuttle</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> MatchTicketing. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenu = document.querySelector('.mobile-menu');
            const navLinks = document.querySelector('.nav-links');
            
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar')) {
                    navLinks.classList.remove('active');
                }
            });
            
            // Close alert messages
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert').remove();
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.remove();
                });
            }, 5000);
            
            // Form elements
            const transportType = document.querySelectorAll('input[name="transport_type"]');
            const fromLocation = document.getElementById('from_location');
            const toLocation = document.getElementById('to_location');
            const departureDate = document.getElementById('departure_date');
            const departureTime = document.getElementById('departure_time');
            const quantity = document.getElementById('quantity');
            const price = document.getElementById('price');
            const submitBtn = document.getElementById('submitBtn');
            
            // Price and time mapping
            const priceMap = {
                'metro': 5.00,
                'bus': 10.00,
                'train': 25.00,
                'car_rental': 50.00,
                'taxi': 25.00,
                'shuttle': 15.00
            };
            
            const timeMap = {
                'metro': '15-30 minutes',
                'bus': '30-60 minutes',
                'train': '1-3 hours',
                'car_rental': '2-4 hours',
                'taxi': '30-60 minutes',
                'shuttle': '45-90 minutes'
            };
            
            const distanceMap = {
                'metro': '5-10 km',
                'bus': '10-20 km',
                'train': '50-200 km',
                'car_rental': '100-300 km',
                'taxi': '10-30 km',
                'shuttle': '20-50 km'
            };
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            departureDate.min = today;
            
            // Set default departure time to next hour
            if (!departureTime.value) {
                const now = new Date();
                now.setHours(now.getHours() + 1);
                now.setMinutes(0);
                departureTime.value = now.toTimeString().slice(0,5);
            }
            
            // Update summary function
            function updateTransportSummary() {
                // Update transport type
                const selectedType = document.querySelector('input[name="transport_type"]:checked');
                if (selectedType) {
                    const typeValue = selectedType.value;
                    const typeText = selectedType.parentElement.querySelector('span').textContent;
                    document.getElementById('summary_type').textContent = typeText;
                    
                    // Update price based on transport type
                    if (priceMap[typeValue]) {
                        price.value = priceMap[typeValue].toFixed(2);
                    }
                    
                    // Update estimated time and distance
                    document.getElementById('estimated_time').textContent = timeMap[typeValue] || '-';
                    document.getElementById('estimated_distance').textContent = distanceMap[typeValue] || '-';
                }
                
                // Update route
                document.getElementById('summary_route').textContent = 
                    `${fromLocation.value || '-'} to ${toLocation.value || '-'}`;
                
                // Update date and time
                const date = departureDate.value || '-';
                const time = departureTime.value || '';
                document.getElementById('summary_datetime').textContent = `${date} ${time}`;
                
                // Update passengers
                document.getElementById('summary_passengers').textContent = quantity.value;
                
                // Calculate total
                const total = (parseFloat(price.value) || 0) * (parseInt(quantity.value) || 1);
                document.getElementById('total_amount').textContent = '$' + total.toFixed(2);
                document.getElementById('total_input').value = total.toFixed(2);
            }
            
            // Event listeners for live updates
            transportType.forEach(input => {
                input.addEventListener('change', updateTransportSummary);
            });
            
            [fromLocation, toLocation, departureDate, departureTime, quantity, price].forEach(input => {
                input.addEventListener('input', updateTransportSummary);
                input.addEventListener('change', updateTransportSummary);
            });
            
            // Form validation
            document.getElementById('transportBookingForm').addEventListener('submit', function(e) {
                if (!document.querySelector('input[name="transport_type"]:checked')) {
                    e.preventDefault();
                    showNotification('Please select a transport type', 'error');
                    return false;
                }
                
                const departureDateTime = new Date(departureDate.value + 'T' + departureTime.value);
                if (departureDateTime < new Date()) {
                    e.preventDefault();
                    showNotification('Departure date and time must be in the future', 'error');
                    return false;
                }
                
                if (fromLocation.value.toLowerCase() === toLocation.value.toLowerCase()) {
                    e.preventDefault();
                    showNotification('From and To locations cannot be the same', 'error');
                    return false;
                }
                
                // Show loading state
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                return true;
            });
            
            // Cancel booking functionality
            document.querySelectorAll('.cancel-booking').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    if (confirm('Are you sure you want to cancel this booking?')) {
                        // In a real application, you would make an AJAX call here
                        showNotification('Booking cancellation requested', 'info');
                    }
                });
            });
            
            // View receipt functionality
            document.querySelectorAll('.view-receipt').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    window.open(`receipt.php?type=transport&id=${bookingId}`, '_blank');
                });
            });
            
            // Initialize summary
            updateTransportSummary();
            
            // Notification function
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'warning'} position-fixed top-0 end-0 m-3`;
                notification.style.zIndex = '9999';
                notification.style.minWidth = '300px';
                notification.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                        <div>${message}</div>
                        <button type="button" class="close-alert ms-auto">&times;</button>
                    </div>
                `;
                document.body.appendChild(notification);
                
                notification.querySelector('.close-alert').addEventListener('click', function() {
                    notification.remove();
                });
                
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>