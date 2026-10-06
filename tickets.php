<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once 'config.php';

// Fetch available matches from database
$matches = [];
$categories = [];
$stadiums = [];

try {
    // Fetch matches with future dates
    $stmt = $pdo->query("SELECT * FROM matches WHERE match_date >= CURDATE() ORDER BY match_date ASC, match_time ASC");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch unique categories for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT category FROM matches WHERE match_date >= CURDATE() ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Fetch unique stadiums for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT stadium FROM matches WHERE match_date >= CURDATE() ORDER BY stadium");
    $stadiums = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // If matches table doesn't exist or error occurs, use empty arrays
}

// If no matches in database, show empty state
$hasMatches = !empty($matches);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Tickets - MatchTicketing</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
       <link rel="stylesheet" href="css/guidestyle.css">
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
                        url('https://images.unsplash.com/photo-1518604666860-9ed391f76460?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
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

        /* ===== Matches Grid ===== */
        .matches-container {
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

        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        /* ===== Match Card ===== */
        .match-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s;
            position: relative;
        }

        .match-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .match-card.featured {
            border: 3px solid var(--accent);
            position: relative;
        }

        .match-card.featured::before {
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

        .match-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .match-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }

        .match-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .match-content {
            padding: 1.5rem;
        }

        .match-teams {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .team {
            text-align: center;
            flex: 1;
        }

        .team-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .team-logo {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 50%;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
        }

        .vs {
            font-weight: 800;
            color: var(--accent);
            font-size: 1.2rem;
            margin: 0 1rem;
        }

        .match-details {
            margin-bottom: 1.5rem;
        }

        .match-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.5rem;
            color: #666;
            font-size: 0.95rem;
        }

        .match-detail i {
            color: var(--primary);
            width: 20px;
        }

        .match-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .price span {
            font-size: 0.9rem;
            color: #666;
            font-weight: 400;
        }

        .available-tickets {
            color: var(--success);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .available-tickets.low {
            color: var(--warning);
        }

        .available-tickets.sold-out {
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

        .btn-add-match {
            background: linear-gradient(to right, var(--accent), #ff8b35);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-add-match:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,53,0.3);
            color: white;
        }

        /* ===== Featured Matches ===== */
        .featured-matches {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 4rem 0;
            margin: 4rem 0;
        }

        .featured-matches .section-title {
            color: white;
        }

        .featured-matches .section-title::after {
            background: var(--accent);
        }

        .featured-matches .match-card {
            background: rgba(255,255,255,0.95);
        }

        /* ===== Upcoming Matches ===== */
        .upcoming-matches {
            margin-bottom: 4rem;
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
            .matches-grid {
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
            
            .matches-grid {
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
            
            .team-name {
                font-size: 1rem;
            }
            
            .vs {
                margin: 0 0.5rem;
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
            <h1>Get Your Match Tickets</h1>
            <p>Experience the thrill of live sports with premium seating and exclusive packages</p>
        </div>
    </section>

    <!-- ===== Filter Section ===== -->
    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> Find Your Perfect Match</h3>
        <form id="matchFilterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-futbol"></i> Sport Category
                    </label>
                    <select class="filter-select" id="filterCategory">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars(strtolower($category)); ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar"></i> Date
                    </label>
                    <select class="filter-select" id="filterDate">
                        <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="weekend">This Weekend</option>
                        <option value="week">Next 7 Days</option>
                        <option value="month">Next 30 Days</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt"></i> Stadium
                    </label>
                    <select class="filter-select" id="filterStadium">
                        <option value="">All Stadiums</option>
                        <?php foreach($stadiums as $stadium): ?>
                            <option value="<?php echo htmlspecialchars(strtolower($stadium)); ?>">
                                <?php echo htmlspecialchars($stadium); ?>
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
                        <option value="0-50">Under $50</option>
                        <option value="50-100">$50 - $100</option>
                        <option value="100-200">$100 - $200</option>
                        <option value="200+">$200+</option>
                    </select>
                </div>
            </div>
            <button type="button" class="filter-btn" id="applyFilters">
                <i class="fas fa-search"></i> Search Matches
            </button>
        </form>
    </div>

    <!-- ===== Matches Container ===== -->
    <div class="matches-container">
        <?php if(!$hasMatches): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3>No Matches Available</h3>
            <p>There are currently no upcoming matches available. Please check back later or contact us for more information.</p>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
            <a href="admin/add_match.php" class="btn-add-match">
                <i class="fas fa-plus"></i> Add New Match
            </a>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        
        <!-- Featured Matches (First 3 matches) -->
        <section class="featured-matches">
            <div class="container">
                <h2 class="section-title">Featured Matches</h2>
                <div class="matches-grid">
                    <?php 
                    $featuredMatches = array_slice($matches, 0, 3);
                    foreach($featuredMatches as $match): 
                        $isSoldOut = $match['available_tickets'] <= 0;
                        $isLowStock = $match['available_tickets'] <= 50 && $match['available_tickets'] > 0;
                        $matchDate = date('F j, Y', strtotime($match['match_date']));
                        $matchTime = date('g:i A', strtotime($match['match_time']));
                    ?>
                    <div class="match-card featured">
                        <div class="match-image" style="background-image: url('<?php echo !empty($match['image']) ? htmlspecialchars($match['image']) : 'https://images.unsplash.com/photo-1518604666860-9ed391f76460?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; ?>');">
                            <span class="match-category"><?php echo htmlspecialchars($match['category']); ?></span>
                        </div>
                        <div class="match-content">
                            <div class="match-teams">
                                <div class="team">
                                    <div class="team-logo">
                                        <?php echo getSportIcon($match['category']); ?>
                                    </div>
                                    <div class="team-name"><?php echo htmlspecialchars($match['team1']); ?></div>
                                </div>
                                <div class="vs">VS</div>
                                <div class="team">
                                    <div class="team-logo">
                                        <?php echo getSportIcon($match['category']); ?>
                                    </div>
                                    <div class="team-name"><?php echo htmlspecialchars($match['team2']); ?></div>
                                </div>
                            </div>
                            
                            <div class="match-details">
                                <div class="match-detail">
                                    <i class="fas fa-trophy"></i>
                                    <span><?php echo htmlspecialchars($match['competition']); ?></span>
                                </div>
                                <div class="match-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($match['stadium']); ?></span>
                                </div>
                                <div class="match-detail">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo $matchDate; ?></span>
                                </div>
                                <div class="match-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $matchTime; ?></span>
                                </div>
                            </div>
                            
                            <div class="match-info">
                                <div class="price">
                                    $<?php echo number_format($match['base_price'], 2); ?>
                                    <span>from</span>
                                </div>
                                <div class="available-tickets <?php echo $isSoldOut ? 'sold-out' : ($isLowStock ? 'low' : ''); ?>">
                                    <i class="fas fa-ticket-alt"></i>
                                    <?php if($isSoldOut): ?>
                                        SOLD OUT
                                    <?php else: ?>
                                        <?php echo $match['available_tickets']; ?> tickets left
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <button class="book-btn" onclick="bookTicket(<?php echo $match['id']; ?>)" <?php echo $isSoldOut ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo $isSoldOut ? 'Sold Out' : 'Book Now'; ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Upcoming Matches (Rest of matches) -->
        <section class="upcoming-matches">
            <h2 class="section-title">Upcoming Matches</h2>
            <div class="matches-grid">
                <?php 
                $upcomingMatches = array_slice($matches, 3);
                foreach($upcomingMatches as $match): 
                    $isSoldOut = $match['available_tickets'] <= 0;
                    $isLowStock = $match['available_tickets'] <= 50 && $match['available_tickets'] > 0;
                    $matchDate = date('F j, Y', strtotime($match['match_date']));
                    $matchTime = date('g:i A', strtotime($match['match_time']));
                ?>
                <div class="match-card">
                    <div class="match-image" style="background-image: url('<?php echo !empty($match['image']) ? htmlspecialchars($match['image']) : 'https://images.unsplash.com/photo-1518604666860-9ed391f76460?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; ?>');">
                        <span class="match-category"><?php echo htmlspecialchars($match['category']); ?></span>
                    </div>
                    <div class="match-content">
                        <div class="match-teams">
                            <div class="team">
                                <div class="team-logo">
                                    <?php echo getSportIcon($match['category']); ?>
                                </div>
                                <div class="team-name"><?php echo htmlspecialchars($match['team1']); ?></div>
                            </div>
                            <div class="vs">VS</div>
                            <div class="team">
                                <div class="team-logo">
                                    <?php echo getSportIcon($match['category']); ?>
                                </div>
                                <div class="team-name"><?php echo htmlspecialchars($match['team2']); ?></div>
                            </div>
                        </div>
                        
                        <div class="match-details">
                            <div class="match-detail">
                                <i class="fas fa-trophy"></i>
                                <span><?php echo htmlspecialchars($match['competition']); ?></span>
                            </div>
                            <div class="match-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($match['stadium']); ?></span>
                            </div>
                            <div class="match-detail">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo $matchDate; ?></span>
                            </div>
                            <div class="match-detail">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $matchTime; ?></span>
                            </div>
                        </div>
                        
                        <div class="match-info">
                            <div class="price">
                                $<?php echo number_format($match['base_price'], 2); ?>
                                <span>from</span>
                            </div>
                            <div class="available-tickets <?php echo $isSoldOut ? 'sold-out' : ($isLowStock ? 'low' : ''); ?>">
                                <i class="fas fa-ticket-alt"></i>
                                <?php if($isSoldOut): ?>
                                    SOLD OUT
                                <?php else: ?>
                                    <?php echo $match['available_tickets']; ?> tickets left
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <button class="book-btn" onclick="bookTicket(<?php echo $match['id']; ?>)" <?php echo $isSoldOut ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
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
                    <h3>Popular Sports</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-futbol"></i> Football</a></li>
                        <li><a href="#"><i class="fas fa-basketball-ball"></i> Basketball</a></li>
                        <li><a href="#"><i class="fas fa-table-tennis"></i> Tennis</a></li>
                        <li><a href="#"><i class="fas fa-baseball-ball"></i> Baseball</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 MatchTicketing. All rights reserved.</p>
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
            
            <?php if($hasMatches): ?>
            // Initialize filter functionality
            initializeFilters();
            <?php endif; ?>
        });

        <?php if($hasMatches): ?>
        // Filter functionality
        function initializeFilters() {
            const applyFiltersBtn = document.getElementById('applyFilters');
            const matchCards = document.querySelectorAll('.match-card');
            
            applyFiltersBtn.addEventListener('click', function() {
                const category = document.getElementById('filterCategory').value.toLowerCase();
                const dateFilter = document.getElementById('filterDate').value;
                const stadium = document.getElementById('filterStadium').value.toLowerCase();
                const priceRange = document.getElementById('filterPrice').value;
                
                matchCards.forEach(card => {
                    let showCard = true;
                    
                    // Category filter
                    if (category) {
                        const cardCategory = card.querySelector('.match-category').textContent.toLowerCase();
                        if (!cardCategory.includes(category)) {
                            showCard = false;
                        }
                    }
                    
                    // Date filter (simplified for demo)
                    if (dateFilter && showCard) {
                        const dateText = card.querySelector('.match-detail:nth-child(3) span').textContent;
                        const date = new Date(dateText);
                        const today = new Date();
                        
                        switch(dateFilter) {
                            case 'today':
                                if (date.toDateString() !== today.toDateString()) showCard = false;
                                break;
                            case 'weekend':
                                const day = date.getDay();
                                if (day !== 0 && day !== 6) showCard = false; // 0 = Sunday, 6 = Saturday
                                break;
                            case 'week':
                                const weekLater = new Date();
                                weekLater.setDate(today.getDate() + 7);
                                if (date < today || date > weekLater) showCard = false;
                                break;
                            case 'month':
                                const monthLater = new Date();
                                monthLater.setDate(today.getDate() + 30);
                                if (date < today || date > monthLater) showCard = false;
                                break;
                        }
                    }
                    
                    // Stadium filter
                    if (stadium && showCard) {
                        const cardStadium = card.querySelector('.match-detail:nth-child(2) span').textContent.toLowerCase();
                        if (!cardStadium.includes(stadium)) {
                            showCard = false;
                        }
                    }
                    
                    // Price filter
                    if (priceRange && showCard) {
                        const priceText = card.querySelector('.price').textContent;
                        const price = parseFloat(priceText.replace('$', '').split(' ')[0]);
                        
                        switch(priceRange) {
                            case '0-50':
                                if (price > 50) showCard = false;
                                break;
                            case '50-100':
                                if (price < 50 || price > 100) showCard = false;
                                break;
                            case '100-200':
                                if (price < 100 || price > 200) showCard = false;
                                break;
                            case '200+':
                                if (price < 200) showCard = false;
                                break;
                        }
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
                
                // Show message if no matches found
                const visibleCards = Array.from(matchCards).filter(card => card.style.display !== 'none');
                if (visibleCards.length === 0) {
                    showNoMatchesMessage();
                } else {
                    removeNoMatchesMessage();
                }
            });
        }

        function showNoMatchesMessage() {
            removeNoMatchesMessage();
            const container = document.querySelector('.matches-container');
            const message = document.createElement('div');
            message.className = 'alert alert-info text-center';
            message.innerHTML = '<i class="fas fa-info-circle me-2"></i> No matches found with the selected filters. Try different criteria.';
            container.appendChild(message);
        }

        function removeNoMatchesMessage() {
            const existingMessage = document.querySelector('.matches-container .alert');
            if (existingMessage) {
                existingMessage.remove();
            }
        }
        <?php endif; ?>

        // Book ticket function
        function bookTicket(matchId) {
            <?php if(isset($_SESSION['user_id'])): ?>
                // Redirect to booking page with match ID
                window.location.href = `book_ticket.php?match_id=${matchId}`;
            <?php else: ?>
                // Redirect to login page
                window.location.href = 'login.php?redirect=tickets&match_id=' + matchId;
            <?php endif; ?>
        }

        // Add to cart functionality (for demo)
        document.querySelectorAll('.book-btn').forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', function() {
                    const card = this.closest('.match-card');
                    const matchName = card.querySelector('.team:first-child .team-name').textContent + 
                                     ' vs ' + 
                                     card.querySelector('.team:last-child .team-name').textContent;
                    
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    this.disabled = true;
                    
                    // Simulate API call
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
                        this.style.background = 'linear-gradient(to right, #28a745, #20c997)';
                        
                        // Show notification
                        showNotification(`Added to cart: ${matchName}`);
                        
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

<?php
// Helper function to get sport icon based on category
function getSportIcon($category) {
    $icons = [
        'Football' => 'fa-futbol',
        'Basketball' => 'fa-basketball-ball',
        'Tennis' => 'fa-table-tennis',
        'Cricket' => 'fa-baseball-ball',
        'Rugby' => 'fa-football-ball',
        'Baseball' => 'fa-baseball-ball',
        'Hockey' => 'fa-hockey-puck',
        'Golf' => 'fa-golf-ball',
        'Boxing' => 'fa-boxing-glove',
        'Volleyball' => 'fa-volleyball-ball'
    ];
    
    $icon = $icons[$category] ?? 'fa-ticket-alt';
    return "<i class='fas $icon'></i>";
}
?>