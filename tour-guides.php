<?php
// tour-guides.php - Tour Guides Booking Page
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = "Book Tour Guides - MatchTicketing";

// Database functions with error handling
function getAvailableTourGuides() {
    global $conn;
    
    if (!$conn) return [];
    
    $query = "SELECT * FROM tour_guides 
              WHERE is_available = 1 
              
              ORDER BY rating DESC, experience_years DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) return [];
    
    $guides = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $guides[] = $row;
    }
    
    return $guides;
}

function getGuideLanguages() {
    global $conn;
    
    if (!$conn) return ['English', 'Spanish', 'French', 'German', 'Italian', 'Japanese'];
    
    $query = "SELECT GROUP_CONCAT(DISTINCT languages) as all_langs FROM tour_guides";
    $result = mysqli_query($conn, $query);
    
    if (!$result) return ['English', 'Spanish', 'French'];
    
    $row = mysqli_fetch_assoc($result);
    $allLanguages = explode(',', $row['all_langs'] ?? '');
    
    return array_unique(array_filter(array_map('trim', $allLanguages)));
}

function getGuideCountries() {
    global $conn;
    
    if (!$conn) return ['USA', 'UK', 'France', 'Italy', 'Japan', 'Spain'];
    
    $query = "SELECT DISTINCT country FROM tour_guides ORDER BY country";
    $result = mysqli_query($conn, $query);
    
    if (!$result) return ['USA', 'UK', 'France'];
    
    $countries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $countries[] = $row['country'];
    }
    
    return array_unique(array_filter($countries));
}

function getGuideSpecialties() {
    return ['Historical Tours', 'Food Tours', 'Adventure Tours', 'Cultural Tours', 
            'Architecture Tours', 'Art Tours', 'Photography Tours', 'Family Tours'];
}

function getUserGuideBookings() {
    global $conn;
    
    if (!$conn) return [];
    
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "SELECT b.*, g.name as guide_name, g.country, g.city, g.profile_image,
                     g.hourly_rate, g.email as guide_email, g.phone as guide_phone
              FROM tour_guide_bookings b
              JOIN tour_guides g ON b.guide_id = g.id
              WHERE b.user_id = '$user_id' 
              ORDER BY b.booking_date DESC, b.start_time DESC 
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
$guides = getAvailableTourGuides();
$languages = getGuideLanguages();
$countries = getGuideCountries();
$specialties = getGuideSpecialties();
$user_bookings = getUserGuideBookings();

$hasGuides = !empty($guides);

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_guide']) && $conn) {
    $guide_id = intval($_POST['guide_id']);
    $booking_date = mysqli_real_escape_string($conn, $_POST['booking_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $hours = intval($_POST['hours']);
    $group_size = intval($_POST['group_size']);
    $special_requirements = mysqli_real_escape_string($conn, $_POST['special_requirements'] ?? '');
    $contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $total_amount = floatval($_POST['total_amount']);
    $user_id = $_SESSION['user_id'];
    
    // Get guide's hourly rate
    $guide_query = "SELECT hourly_rate FROM tour_guides WHERE id = '$guide_id'";
    $guide_result = mysqli_query($conn, $guide_query);
    $guide = mysqli_fetch_assoc($guide_result);
    $hourly_rate = $guide['hourly_rate'] ?? 50;
    
    $query = "INSERT INTO tour_guide_bookings (
                user_id,
                guide_id,
                booking_date,
                start_time,
                end_time,
                hours,
                total_amount,
                group_size,
                special_requirements,
                contact_name,
                contact_email,
                contact_phone,
                status,
                payment_status,
                payment_method,
                created_at
              ) VALUES (
                '$user_id',
                '$guide_id',
                '$booking_date',
                '$start_time',
                '$end_time',
                '$hours',
                '$total_amount',
                '$group_size',
                '$special_requirements',
                '$contact_name',
                '$contact_email',
                '$contact_phone',
                'pending',
                'pending',
                'cash',
                NOW()
              )";
    
    if (mysqli_query($conn, $query)) {
        $booking_id = mysqli_insert_id($conn);
        
        // Update guide availability
        $update_query = "UPDATE tour_guides SET is_available = 0 WHERE id = '$guide_id'";
        mysqli_query($conn, $update_query);
        
        $_SESSION['success_message'] = "Tour guide booked successfully! Booking ID: TG" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
        header("Location: tour-guides.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error booking tour guide: " . mysqli_error($conn);
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
   <link rel="stylesheet" href="css/guidestyle.css">
    
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
                <li><a href="tour-guides.php" class="active"><i class="fas fa-user-friends"></i> Tour Guides</a></li>
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
            <h1>Book Professional Tour Guides</h1>
            <p>Experience your destination like a local with our certified tour guides</p>
        </div>
    </section>

    <!-- ===== Search Filters ===== -->
    <div class="booking-container">
        <div class="search-filters">
            <h2 class="form-title">
                <i class="fas fa-search"></i> Find Your Perfect Guide
            </h2>
            
            <form method="GET" id="guideSearchForm">
                <div class="filter-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Country
                        </label>
                        <select class="form-select" name="country" id="country">
                            <option value="">All Countries</option>
                            <?php foreach($countries as $country): ?>
                                <option value="<?php echo htmlspecialchars($country); ?>">
                                    <?php echo htmlspecialchars($country); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-language"></i> Language
                        </label>
                        <select class="form-select" name="language" id="language">
                            <option value="">All Languages</option>
                            <?php foreach($languages as $language): ?>
                                <option value="<?php echo htmlspecialchars($language); ?>">
                                    <?php echo htmlspecialchars($language); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-star"></i> Minimum Rating
                        </label>
                        <select class="form-select" name="min_rating" id="min_rating">
                            <option value="0">Any Rating</option>
                            <option value="3">3+ Stars</option>
                            <option value="4">4+ Stars</option>
                            <option value="4.5">4.5+ Stars</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tags"></i> Specialty
                        </label>
                        <select class="form-select" name="specialty" id="specialty">
                            <option value="">All Specialties</option>
                            <?php foreach($specialties as $specialty): ?>
                                <option value="<?php echo htmlspecialchars($specialty); ?>">
                                    <?php echo htmlspecialchars($specialty); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign"></i> Max Hourly Rate ($)
                        </label>
                        <input type="range" class="form-control" id="price_range" 
                               min="10" max="200" value="100" step="10">
                        <div class="d-flex justify-content-between mt-2">
                            <small>$10</small>
                            <span id="price_value">$100/hour</span>
                            <small>$200</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-briefcase"></i> Min Experience
                        </label>
                        <select class="form-select" name="experience" id="experience">
                            <option value="0">Any Experience</option>
                            <option value="1">1+ years</option>
                            <option value="3">3+ years</option>
                            <option value="5">5+ years</option>
                            <option value="10">10+ years</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Guides
                    </button>
                    <button type="reset" class="btn btn-secondary" id="resetFilters">
                        <i class="fas fa-redo"></i> Reset Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== Guides Grid ===== -->
        <div class="section-title">Available Tour Guides</div>
        
        <?php if (empty($guides)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>No Guides Available</h3>
                <p>We couldn't find any tour guides matching your criteria. Please try different filters or check back later.</p>
            </div>
        <?php else: ?>
            <!-- Guides Grid -->
            <div class="guides-grid" id="guidesGrid">
                <?php foreach($guides as $guide): ?>
                <div class="guide-card" 
                     data-country="<?php echo htmlspecialchars($guide['country']); ?>"
                     data-languages="<?php echo htmlspecialchars($guide['languages']); ?>"
                     data-rating="<?php echo $guide['rating']; ?>"
                     data-specialties="<?php echo htmlspecialchars($guide['specialties']); ?>"
                     data-price="<?php echo $guide['hourly_rate']; ?>"
                     data-experience="<?php echo $guide['experience_years']; ?>">
                    
                    <div class="guide-image" style="background-image: url('<?php echo htmlspecialchars($guide['profile_image'] ?? 'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'); ?>')">
                        <div class="guide-rating">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($guide['rating'], 1); ?>
                        </div>
                    </div>
                    
                    <div class="guide-content">
                        <div class="availability-badge available">
                            Available Now
                        </div>
                        
                        <div class="guide-header">
                            <div>
                                <h3 class="guide-name"><?php echo htmlspecialchars($guide['name']); ?></h3>
                                <div class="guide-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($guide['city'] . ', ' . $guide['country']); ?>
                                </div>
                            </div>
                            <div class="guide-price">
                                <div class="price-amount">$<?php echo number_format($guide['hourly_rate'], 2); ?></div>
                                <div class="price-label">per hour</div>
                            </div>
                        </div>
                        
                        <div class="guide-details">
                            <div class="guide-detail">
                                <i class="fas fa-briefcase"></i>
                                <span><?php echo $guide['experience_years']; ?> years experience</span>
                            </div>
                            <div class="guide-detail">
                                <i class="fas fa-language"></i>
                                <span><?php echo htmlspecialchars($guide['languages']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($guide['specialties'])): 
                            $guide_specialties = explode(',', $guide['specialties']);
                        ?>
                        <div class="specialties">
                            <?php foreach($guide_specialties as $specialty): ?>
                                <span class="specialty-tag"><?php echo htmlspecialchars(trim($specialty)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p style="color: #666; font-size: 0.95rem; margin-bottom: 1rem; line-height: 1.5;">
                            <?php echo htmlspecialchars(substr($guide['description'] ?? 'Professional tour guide with extensive local knowledge.', 0, 100)); ?>...
                        </p>
                        
                        <button class="book-btn" onclick="openBookingModal(<?php echo $guide['id']; ?>)">
                            <i class="fas fa-calendar-check"></i> Book This Guide
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== Recent Bookings ===== -->
    <div class="recent-bookings">
        <h2 class="section-title">My Tour Guide Bookings</h2>
        
        <?php if (empty($user_bookings)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3>No Bookings Yet</h3>
                <p>Your tour guide bookings will appear here once you make a booking. Book your first guide to get started!</p>
            </div>
        <?php else: ?>
            <!-- Bookings Grid -->
            <div class="bookings-grid">
                <?php foreach($user_bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">TG<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
                        <span class="booking-status status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    <div class="booking-content">
                        <div class="booking-details">
                            <div class="booking-detail">
                                <i class="fas fa-user-friends"></i>
                                <span><?php echo htmlspecialchars($booking['guide_name']); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-clock"></i>
                                <span><?php echo date('g:i A', strtotime($booking['start_time'])); ?> - <?php echo date('g:i A', strtotime($booking['end_time'])); ?></span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-users"></i>
                                <span><?php echo $booking['group_size']; ?> person(s)</span>
                            </div>
                            <div class="booking-detail">
                                <i class="fas fa-hourglass-half"></i>
                                <span><?php echo $booking['hours']; ?> hours</span>
                            </div>
                        </div>
                        
                        <div class="booking-total">
                            $<?php echo number_format($booking['total_amount'], 2); ?>
                        </div>
                        
                        <div class="booking-actions">
                            <?php 
                                $bookingDate = new DateTime($booking['booking_date'] . ' ' . $booking['start_time']);
                                $now = new DateTime();
                                $isUpcoming = $bookingDate > $now;
                            ?>
                            
                            <?php if ($isUpcoming && $booking['status'] == 'pending'): ?>
                                <button class="btn-small btn-warning cancel-booking" 
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!$isUpcoming && $booking['status'] == 'completed'): ?>
                                <button class="btn-small btn-secondary add-review" 
                                        data-booking-id="<?php echo $booking['id']; ?>"
                                        data-guide-id="<?php echo $booking['guide_id']; ?>">
                                    <i class="fas fa-star"></i> Add Review
                                </button>
                            <?php endif; ?>
                      
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== Booking Modal ===== -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-calendar-check"></i> Book Tour Guide
                </h3>
                <button class="close-modal" onclick="closeBookingModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="POST">
                    <div class="guide-summary" id="guideSummary">
                        <!-- Guide summary will be populated here -->
                    </div>
                    
                    <input type="hidden" name="guide_id" id="guide_id">
                    <input type="hidden" name="book_guide" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Booking Date
                            </label>
                            <input type="date" class="form-control" name="booking_date" id="booking_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Start Time
                            </label>
                            <input type="time" class="form-control" name="start_time" id="start_time" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-hourglass-half"></i> Duration (hours)
                            </label>
                            <input type="number" class="form-control" name="hours" id="hours" 
                                   min="1" max="12" value="2" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-users"></i> Group Size
                            </label>
                            <input type="number" class="form-control" name="group_size" id="group_size" 
                                   min="1" max="20" value="1" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Contact Name
                            </label>
                            <input type="text" class="form-control" name="contact_name" 
                                   value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Contact Email
                            </label>
                            <input type="email" class="form-control" name="contact_email" 
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Contact Phone
                        </label>
                        <input type="tel" class="form-control" name="contact_phone" 
                               value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Special Requirements
                        </label>
                        <textarea class="form-control" name="special_requirements" 
                                  rows="3" placeholder="Any special requests, accessibility needs, or specific interests..."></textarea>
                    </div>
                    
                    <div class="price-summary" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 style="margin: 0; color: var(--primary);">Total Amount</h4>
                                <small style="color: #666;">Includes all taxes and fees</small>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);" id="totalAmount">$0.00</div>
                                <input type="hidden" name="total_amount" id="total_input">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="book-btn" id="submitBookingBtn">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== Review Modal ===== -->
    <div class="modal" id="reviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-star"></i> Rate Your Experience
                </h3>
                <button class="close-modal" onclick="closeReviewModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <div class="star-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" data-rating="<?php echo $i; ?>" 
                                   style="font-size: 2rem; color: #ddd; cursor: pointer; margin: 0 2px;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Review</label>
                        <textarea class="form-control" name="comment" rows="4" 
                                  placeholder="Share your experience with this guide..."></textarea>
                    </div>
                    
                    <input type="hidden" name="booking_id" id="review_booking_id">
                    <input type="hidden" name="guide_id" id="review_guide_id">
                    
                    <button type="submit" class="book-btn">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </form>
            </div>
        </div>
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
                        <li><a href="tour-guides.php"><i class="fas fa-user-friends"></i> Tour Guides</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Tour Types</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-landmark"></i> Historical Tours</a></li>
                        <li><a href="#"><i class="fas fa-utensils"></i> Food Tours</a></li>
                        <li><a href="#"><i class="fas fa-hiking"></i> Adventure Tours</a></li>
                        <li><a href="#"><i class="fas fa-theater-masks"></i> Cultural Tours</a></li>
                        <li><a href="#"><i class="fas fa-building"></i> Architecture Tours</a></li>
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
            
            // Price range slider
            const priceRange = document.getElementById('price_range');
            const priceValue = document.getElementById('price_value');
            
            if (priceRange) {
                priceRange.addEventListener('input', function() {
                    priceValue.textContent = '$' + this.value + '/hour';
                    filterGuides();
                });
            }
            
            // Filter guides function
            function filterGuides() {
                const country = document.getElementById('country').value.toLowerCase();
                const language = document.getElementById('language').value.toLowerCase();
                const minRating = parseFloat(document.getElementById('min_rating').value) || 0;
                const specialty = document.getElementById('specialty').value.toLowerCase();
                const maxPrice = parseInt(priceRange.value) || 100;
                const experience = parseInt(document.getElementById('experience').value) || 0;
                
                const guideCards = document.querySelectorAll('.guide-card');
                
                guideCards.forEach(card => {
                    const cardCountry = card.dataset.country.toLowerCase();
                    const cardLanguages = card.dataset.languages.toLowerCase();
                    const cardRating = parseFloat(card.dataset.rating) || 0;
                    const cardSpecialties = card.dataset.specialties.toLowerCase();
                    const cardPrice = parseFloat(card.dataset.price) || 0;
                    const cardExperience = parseInt(card.dataset.experience) || 0;
                    
                    let show = true;
                    
                    if (country && !cardCountry.includes(country)) show = false;
                    if (language && !cardLanguages.includes(language)) show = false;
                    if (cardRating < minRating) show = false;
                    if (specialty && !cardSpecialties.includes(specialty)) show = false;
                    if (cardPrice > maxPrice) show = false;
                    if (cardExperience < experience) show = false;
                    
                    card.style.display = show ? 'block' : 'none';
                });
                
                // Check if any guides are visible
                const visibleGuides = Array.from(guideCards).filter(card => card.style.display !== 'none');
                if (visibleGuides.length === 0 && guideCards.length > 0) {
                    showNotification('No guides match your filters. Try adjusting your criteria.', 'warning');
                }
            }
            
            // Add event listeners to filter inputs
            ['country', 'language', 'min_rating', 'specialty', 'experience'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', filterGuides);
                }
            });
            
            // Reset filters button
            const resetFiltersBtn = document.getElementById('resetFilters');
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('guideSearchForm').reset();
                    priceRange.value = 100;
                    priceValue.textContent = '$100/hour';
                    filterGuides();
                });
            }
            
            // Booking modal functionality
            let currentGuide = null;
            let hourlyRate = 0;
            
            window.openBookingModal = function(guideId) {
                // In a real application, you would fetch guide details via AJAX
                // For now, we'll find the guide in the current page data
                const guideCards = document.querySelectorAll('.guide-card');
                guideCards.forEach(card => {
                    if (card.querySelector('.book-btn').onclick.toString().includes(guideId)) {
                        const guideName = card.querySelector('.guide-name').textContent;
                        const guideLocation = card.querySelector('.guide-location').textContent;
                        const guidePrice = card.querySelector('.price-amount').textContent;
                        const guideImage = card.querySelector('.guide-image').style.backgroundImage;
                        const guideExperience = card.querySelector('.guide-detail span').textContent;
                        
                        hourlyRate = parseFloat(guidePrice.replace('$', '')) || 50;
                        
                        document.getElementById('guideSummary').innerHTML = `
                            <img src="${guideImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1')}" 
                                 alt="${guideName}" class="guide-summary-img">
                            <div class="guide-summary-info">
                                <h4>${guideName}</h4>
                                <p><i class="fas fa-map-marker-alt"></i> ${guideLocation}</p>
                                <p><i class="fas fa-briefcase"></i> ${guideExperience}</p>
                                <p><i class="fas fa-dollar-sign"></i> ${guidePrice}/hour</p>
                            </div>
                        `;
                        
                        document.getElementById('guide_id').value = guideId;
                        currentGuide = { id: guideId, name: guideName, rate: hourlyRate };
                        
                        updateTotalAmount();
                    }
                });
                
                // Set default date and time
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('booking_date').value = today;
                document.getElementById('booking_date').min = today;
                
                const now = new Date();
                now.setHours(now.getHours() + 1);
                now.setMinutes(0);
                document.getElementById('start_time').value = now.toTimeString().slice(0,5);
                
                document.getElementById('bookingModal').style.display = 'flex';
            };
            
            window.closeBookingModal = function() {
                document.getElementById('bookingModal').style.display = 'none';
                document.getElementById('bookingForm').reset();
            };
            
            // Close modal when clicking outside
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });
            
            // Calculate total amount
            function updateTotalAmount() {
                const hours = parseInt(document.getElementById('hours').value) || 2;
                const total = hourlyRate * hours;
                document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
                document.getElementById('total_input').value = total.toFixed(2);
            }
            
            // Add event listeners for amount calculation
            document.getElementById('hours')?.addEventListener('input', updateTotalAmount);
            
            // Booking form submission
            document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
                const bookingDate = document.getElementById('booking_date').value;
                const startTime = document.getElementById('start_time').value;
                const departureDateTime = new Date(bookingDate + 'T' + startTime);
                
                if (departureDateTime < new Date()) {
                    e.preventDefault();
                    showNotification('Booking date and time must be in the future', 'error');
                    return false;
                }
                
                // Calculate end time
                const hours = parseInt(document.getElementById('hours').value) || 2;
                const endTime = new Date(departureDateTime.getTime() + hours * 60 * 60 * 1000);
                document.getElementById('end_time').value = endTime.toTimeString().slice(0,5);
                
                // Show loading state
                const submitBtn = document.getElementById('submitBookingBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                return true;
            });
            
            // Review modal functionality
            window.closeReviewModal = function() {
                document.getElementById('reviewModal').style.display = 'none';
            };
            
            // Star rating
            const stars = document.querySelectorAll('.star-rating .fa-star');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    document.getElementById('rating').value = rating;
                    
                    stars.forEach((s, index) => {
                        s.style.color = index < rating ? '#ffc107' : '#ddd';
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const hoverRating = parseInt(this.dataset.rating);
                    stars.forEach((s, index) => {
                        s.style.color = index < hoverRating ? '#ffc107' : '#ddd';
                    });
                });
            });
            
            // Reset stars on mouse leave
            document.querySelector('.star-rating')?.addEventListener('mouseleave', function() {
                const rating = parseInt(document.getElementById('rating').value) || 0;
                stars.forEach((s, index) => {
                    s.style.color = index < rating ? '#ffc107' : '#ddd';
                });
            });
            
            // Add review button functionality
            document.querySelectorAll('.add-review').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.dataset.bookingId;
                    const guideId = this.dataset.guideId;
                    
                    document.getElementById('review_booking_id').value = bookingId;
                    document.getElementById('review_guide_id').value = guideId;
                    
                    document.getElementById('reviewModal').style.display = 'flex';
                });
            });
            
            // Review form submission
            document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const rating = document.getElementById('rating').value;
                if (rating < 1) {
                    showNotification('Please select a rating', 'error');
                    return;
                }
                
                // In a real application, you would submit via AJAX
                showNotification('Thank you for your review!', 'success');
                closeReviewModal();
            });
            
            // Cancel booking functionality
            document.querySelectorAll('.cancel-booking').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.dataset.bookingId;
                    if (confirm('Are you sure you want to cancel this booking?')) {
                        // In a real application, you would make an AJAX call here
                        showNotification('Booking cancellation requested. Our team will contact you shortly.', 'info');
                    }
                });
            });
            
            // View details functionality
            document.querySelectorAll('.view-details').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.dataset.bookingId;
                    window.open(`get-guide-details.php?id=${bookingId}&type=guide`, '_blank');
                });
            });
            
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