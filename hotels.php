<?php
// hotels.php
require_once 'config.php';

// Fetch all available hotels from database using the function from config.php
$hotels = getAvailableHotels();
$locations = getHotelLocations(); // This function should be in your config.php
$room_types = ['Single', 'Double', 'Suite', 'Deluxe', 'Family', 'Executive'];

// If you don't have getHotelLocations() in config.php, add this function:
if (!function_exists('getHotelLocations') && empty($locations)) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT DISTINCT location FROM hotels WHERE available_rooms > 0 ORDER BY location");
        $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error fetching locations: " . $e->getMessage());
        $locations = [];
    }
}

$hasHotels = !empty($hotels);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking - MatchTicketing</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
       <link rel="stylesheet" href="css/hotel-style.css">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #283593;
            --accent: #ff6b35;
            --success: #28a745;
            --warning: #ffc107;
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
                        url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
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

        /* ===== Filter Section ===== */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: -50px auto 3rem;
            max-width: 1200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }

        .filter-section h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            margin-bottom: 1rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(26,35,126,0.25);
            outline: none;
        }

        .filter-btn {
            background: linear-gradient(to right, var(--accent), #ff8b35);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 1rem;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,53,0.3);
        }

        /* ===== Hotels Grid ===== */
        .hotels-container {
            max-width: 1200px;
            margin: 0 auto 4rem;
            padding: 0 20px;
        }

        .section-title {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--accent), #ff8b35);
            border-radius: 2px;
        }

        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        /* ===== Hotel Card ===== */
        .hotel-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s;
            position: relative;
        }

        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .hotel-card.featured {
            border: 3px solid var(--accent);
            position: relative;
        }

        .hotel-card.featured::before {
            content: 'FEATURED';
            position: absolute;
            top: 15px;
            left: -30px;
            background: var(--accent);
            color: white;
            padding: 5px 30px;
            font-size: 0.8rem;
            font-weight: 600;
            transform: rotate(-45deg);
            z-index: 2;
        }

        .hotel-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .hotel-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }

        .hotel-rating {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .hotel-content {
            padding: 1.5rem;
        }

        .hotel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .hotel-name {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .hotel-location {
            color: #666;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .hotel-price {
            text-align: right;
        }

        .price-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .price-label {
            font-size: 0.9rem;
            color: #666;
        }

        .hotel-details {
            margin-bottom: 1.5rem;
        }

        .hotel-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.5rem;
            color: #666;
            font-size: 0.95rem;
        }

        .hotel-detail i {
            color: var(--primary);
            width: 20px;
        }

        .amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .amenity {
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #666;
        }

        .hotel-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .available-rooms {
            color: var(--success);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .available-rooms.low {
            color: var(--warning);
        }

        .available-rooms.sold-out {
            color: #dc3545;
        }

        .book-btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 1rem;
        }

        .book-btn:hover {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,35,126,0.3);
        }

        .book-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ===== Featured Hotels ===== */
        .featured-hotels {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 4rem 0;
            margin: 4rem 0;
        }

        .featured-hotels .section-title {
            color: white;
        }

        .featured-hotels .section-title::after {
            background: var(--accent);
        }

        .featured-hotels .hotel-card {
            background: rgba(255,255,255,0.95);
        }

        /* ===== Footer ===== */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-section p {
            color: #adb5bd;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section ul li a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-section ul li a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #495057;
            color: #adb5bd;
            font-size: 0.9rem;
        }

        /* ===== Alert Messages ===== */
        .alert-container {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 20px;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 992px) {
            .hotels-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
            
            .nav-links {
                position: fixed;
                top: 60px;
                left: 0;
                width: 100%;
                background: var(--primary);
                flex-direction: column;
                padding: 2rem;
                display: none;
                gap: 1rem;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .page-header h1 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 6rem 0 3rem;
            }
            
            .page-header h1 {
                font-size: 2.2rem;
            }
            
            .page-header p {
                font-size: 1.1rem;
            }
            
            .filter-section {
                padding: 1.5rem;
                margin: -30px auto 2rem;
            }
            
            .hotels-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .hotel-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hotel-price {
                text-align: left;
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
    <!-- ===== Page Header ===== -->
    <section class="page-header">
        <div class="container">
            <h1>Find Your Perfect Stay</h1>
            <p>Comfortable hotels near stadiums for the ultimate match experience</p>
        </div>
    </section>

    <!-- ===== Filter Section ===== -->
    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> Find Your Perfect Hotel</h3>
        <form id="hotelFilterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </label>
                    <select class="filter-select" id="filterLocation">
                        <option value="">All Locations</option>
                        <?php foreach($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars(strtolower($location)); ?>">
                                <?php echo htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-bed"></i> Room Type
                    </label>
                    <select class="filter-select" id="filterRoomType">
                        <option value="">All Room Types</option>
                        <?php foreach($room_types as $type): ?>
                            <option value="<?php echo htmlspecialchars(strtolower($type)); ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-dollar-sign"></i> Price Range
                    </label>
                    <select class="filter-select" id="filterPrice">
                        <option value="">All Prices</option>
                        <option value="0-100">Under $100</option>
                        <option value="100-200">$100 - $200</option>
                        <option value="200-300">$200 - $300</option>
                        <option value="300+">$300+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-star"></i> Minimum Rating
                    </label>
                    <select class="filter-select" id="filterRating">
                        <option value="">Any Rating</option>
                        <option value="4.5">4.5+ Stars</option>
                        <option value="4.0">4.0+ Stars</option>
                        <option value="3.5">3.5+ Stars</option>
                        <option value="3.0">3.0+ Stars</option>
                    </select>
                </div>
            </div>
            <button type="button" class="filter-btn" id="applyFilters">
                <i class="fas fa-search"></i> Search Hotels
            </button>
        </form>
    </div>

    <!-- ===== Hotels Container ===== -->
    <div class="hotels-container">
        <?php if(!$hasHotels): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-hotel"></i>
            </div>
            <h3>No Hotels Available</h3>
            <p>There are currently no hotels available for booking. Please check back later or contact us for more information.</p>
            <a href="insert_hotels.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Sample Hotels
            </a>
        </div>
        
        <?php else: ?>
        
        <!-- Display hotel count -->
        <div class="text-center mb-4">
            <p class="lead">Found <strong><?php echo count($hotels); ?></strong> hotels available for booking</p>
        </div>
        
        <!-- Featured Hotels (First 3 hotels) -->
        <section class="featured-hotels">
            <div class="container">
                <h2 class="section-title">Featured Hotels</h2>
                <div class="hotels-grid">
                    <?php 
                    // Get first 3 hotels as featured
                    $featuredHotels = array_slice($hotels, 0, 3);
                    foreach($featuredHotels as $hotel): 
                        $isSoldOut = $hotel['available_rooms'] <= 0;
                        $isLowStock = $hotel['available_rooms'] <= 5 && $hotel['available_rooms'] > 0;
                        $amenities = isset($hotel['amenities']) ? explode(', ', $hotel['amenities']) : ['Free WiFi', 'Parking', 'Restaurant'];
                    ?>
                    <div class="hotel-card featured">
                        <div class="hotel-image" style="background-image: url('<?php echo $hotel['image'] . '?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; ?>');">
                            <div class="hotel-rating">
                                <i class="fas fa-star"></i>
                                <?php echo number_format($hotel['rating'], 1); ?>
                            </div>
                        </div>
                        <div class="hotel-content">
                            <div class="hotel-header">
                                <div>
                                    <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                    <p class="hotel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($hotel['location']); ?>
                                        <?php if(isset($hotel['distance_to_stadium'])): ?>
                                        <span class="text-muted">• <?php echo htmlspecialchars($hotel['distance_to_stadium']); ?> to stadium</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="hotel-price">
                                    <div class="price-amount">$<?php echo number_format($hotel['price_per_night'], 0); ?></div>
                                    <div class="price-label">per night</div>
                                </div>
                            </div>
                            
                            <div class="hotel-details">
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                
                                <div class="amenities">
                                    <?php foreach(array_slice($amenities, 0, 4) as $amenity): ?>
                                    <span class="amenity"><?php echo htmlspecialchars($amenity); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="hotel-detail">
                                    <i class="fas fa-door-open"></i>
                                    <span>Available Rooms: <strong><?php echo $hotel['available_rooms']; ?></strong></span>
                                </div>
                                
                                <?php if(isset($hotel['distance_to_stadium'])): ?>
                                <div class="hotel-detail">
                                    <i class="fas fa-walking"></i>
                                    <span><?php echo htmlspecialchars($hotel['distance_to_stadium']); ?> to stadium</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hotel-info">
                                <div class="available-rooms <?php echo $isSoldOut ? 'sold-out' : ($isLowStock ? 'low' : ''); ?>">
                                    <i class="fas fa-bed"></i>
                                    <?php if($isSoldOut): ?>
                                        SOLD OUT
                                    <?php else: ?>
                                        <?php echo $hotel['available_rooms']; ?> rooms left
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <button class="book-btn" onclick="bookHotel(<?php echo $hotel['id']; ?>)" <?php echo $isSoldOut ? 'disabled' : ''; ?>>
                                <i class="fas fa-calendar-check"></i>
                                <?php echo $isSoldOut ? 'Sold Out' : 'Book Now'; ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- All Hotels -->
        <section class="upcoming-matches">
            <h2 class="section-title">All Hotels</h2>
            <div class="hotels-grid">
                <?php 
                // Get all hotels except first 3 (which are featured)
                $allHotels = array_slice($hotels, 3);
                foreach($allHotels as $hotel): 
                    $isSoldOut = $hotel['available_rooms'] <= 0;
                    $isLowStock = $hotel['available_rooms'] <= 5 && $hotel['available_rooms'] > 0;
                    $amenities = isset($hotel['amenities']) ? explode(', ', $hotel['amenities']) : ['Free WiFi', 'Parking', 'Restaurant'];
                ?>
                <div class="hotel-card">
                    <div class="hotel-image" style="background-image: url('<?php echo $hotel['image'] . '?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; ?>');">
                        <div class="hotel-rating">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($hotel['rating'], 1); ?>
                        </div>
                    </div>
                    <div class="hotel-content">
                        <div class="hotel-header">
                            <div>
                                <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                <p class="hotel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($hotel['location']); ?>
                                    <?php if(isset($hotel['distance_to_stadium'])): ?>
                                    <span class="text-muted">• <?php echo htmlspecialchars($hotel['distance_to_stadium']); ?> to stadium</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="hotel-price">
                                <div class="price-amount">$<?php echo number_format($hotel['price_per_night'], 0); ?></div>
                                <div class="price-label">per night</div>
                            </div>
                        </div>
                        
                        <div class="hotel-details">
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($hotel['description']); ?></p>
                            
                            <div class="amenities">
                                <?php foreach(array_slice($amenities, 0, 3) as $amenity): ?>
                                <span class="amenity"><?php echo htmlspecialchars($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="hotel-detail">
                                <i class="fas fa-door-open"></i>
                                <span>Available Rooms: <strong><?php echo $hotel['available_rooms']; ?></strong></span>
                            </div>
                            
                            <?php if(isset($hotel['distance_to_stadium'])): ?>
                            <div class="hotel-detail">
                                <i class="fas fa-walking"></i>
                                <span><?php echo htmlspecialchars($hotel['distance_to_stadium']); ?> to stadium</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="hotel-info">
                            <div class="available-rooms <?php echo $isSoldOut ? 'sold-out' : ($isLowStock ? 'low' : ''); ?>">
                                <i class="fas fa-bed"></i>
                                <?php if($isSoldOut): ?>
                                    SOLD OUT
                                <?php else: ?>
                                    <?php echo $hotel['available_rooms']; ?> rooms left
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <button class="book-btn" onclick="bookHotel(<?php echo $hotel['id']; ?>)" <?php echo $isSoldOut ? 'disabled' : ''; ?>>
                            <i class="fas fa-calendar-check"></i>
                            <?php echo $isSoldOut ? 'Sold Out' : 'Book Now'; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
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
                    <h3>Popular Hotels</h3>
                    <ul>
                        <?php 
                        // Display top 4 rated hotels in footer
                        $topHotels = array_slice($hotels, 0, 4);
                        foreach($topHotels as $hotel): 
                        ?>
                        <li><a href="book_hotel.php?hotel_id=<?php echo $hotel['id']; ?>">
                            <i class="fas fa-star"></i> <?php echo htmlspecialchars($hotel['name']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 MatchTicketing. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
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
            
            <?php if($hasHotels): ?>
            // Initialize filter functionality
            initializeFilters();
            <?php endif; ?>
        });

        <?php if($hasHotels): ?>
        // Filter functionality
        function initializeFilters() {
            const applyFiltersBtn = document.getElementById('applyFilters');
            const hotelCards = document.querySelectorAll('.hotel-card');
            
            applyFiltersBtn.addEventListener('click', function() {
                const location = document.getElementById('filterLocation').value.toLowerCase();
                const roomType = document.getElementById('filterRoomType').value.toLowerCase();
                const priceRange = document.getElementById('filterPrice').value;
                const minRating = parseFloat(document.getElementById('filterRating').value) || 0;
                
                hotelCards.forEach(card => {
                    let showCard = true;
                    
                    // Location filter
                    if (location) {
                        const cardLocation = card.querySelector('.hotel-location').textContent.toLowerCase();
                        if (!cardLocation.includes(location)) {
                            showCard = false;
                        }
                    }
                    
                    // Room type filter (simplified - in real app, hotels would have room types)
                    if (roomType && showCard) {
                        // This would check hotel room types in a real application
                        // For demo, we'll pass all cards
                    }
                    
                    // Price filter
                    if (priceRange && showCard) {
                        const priceText = card.querySelector('.price-amount').textContent;
                        const price = parseFloat(priceText.replace('$', '').replace(',', ''));
                        
                        switch(priceRange) {
                            case '0-100':
                                if (price > 100) showCard = false;
                                break;
                            case '100-200':
                                if (price < 100 || price > 200) showCard = false;
                                break;
                            case '200-300':
                                if (price < 200 || price > 300) showCard = false;
                                break;
                            case '300+':
                                if (price < 300) showCard = false;
                                break;
                        }
                    }
                    
                    // Rating filter
                    if (minRating > 0 && showCard) {
                        const ratingText = card.querySelector('.hotel-rating').textContent;
                        const rating = parseFloat(ratingText);
                        if (rating < minRating) showCard = false;
                    }
                    
                    // Show/hide card
                    card.style.display = showCard ? 'block' : 'none';
                    if (!showCard) {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                    } else {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }
                });
                
                // Show message if no hotels found
                const visibleCards = Array.from(hotelCards).filter(card => card.style.display !== 'none');
                if (visibleCards.length === 0) {
                    showNoHotelsMessage();
                } else {
                    removeNoHotelsMessage();
                }
            });
        }

        function showNoHotelsMessage() {
            removeNoHotelsMessage();
            const container = document.querySelector('.hotels-container');
            const message = document.createElement('div');
            message.className = 'alert alert-info text-center mt-4';
            message.innerHTML = '<i class="fas fa-info-circle me-2"></i> No hotels found with the selected filters. Try different criteria.';
            container.appendChild(message);
        }

        function removeNoHotelsMessage() {
            const existingMessage = document.querySelector('.hotels-container .alert');
            if (existingMessage) {
                existingMessage.remove();
            }
        }
        <?php endif; ?>

        // Book hotel function
        function bookHotel(hotelId) {
            <?php if(isset($_SESSION['user_id'])): ?>
                // Redirect to hotel booking page with hotel ID
                window.location.href = `book_hotel.php?hotel_id=${hotelId}`;
            <?php else: ?>
                // Redirect to login page
                window.location.href = 'login.php?redirect=hotels&hotel_id=' + hotelId;
            <?php endif; ?>
        }

        // Add to cart functionality (for demo)
        document.querySelectorAll('.book-btn').forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', function() {
                    const card = this.closest('.hotel-card');
                    const hotelName = card.querySelector('.hotel-name').textContent;
                    
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    this.disabled = true;
                    
                    // Simulate API call
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
                        this.style.background = 'linear-gradient(to right, #28a745, #20c997)';
                        
                        // Show notification
                        showNotification(`Added to cart: ${hotelName}`);
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                            this.style.background = '';
                        }, 2000);
                    }, 1000);
                });
            }
        });

        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'position-fixed top-0 end-0 p-3';
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>