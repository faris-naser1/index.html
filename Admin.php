<?php
// admin.php - Admin Dashboard
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['username'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = "Admin Dashboard - MatchTicketing";

// Get all data for admin
function getAllTransportBookings() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT tb.*, 
                   u.username,
                   u.email,
                   u.full_name,
                   t.vehicle_name,
                   t.registration_no
            FROM transport_bookings tb
            LEFT JOIN users u ON tb.user_id = u.id
            LEFT JOIN transport t ON tb.vehicle_id = t.id
            ORDER BY tb.booking_date DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching transport bookings: " . $e->getMessage());
        return [];
    }
}

function getAllHotelBookings() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT hb.*,
                   u.username,
                   u.email,
                   u.full_name,
                   h.name as hotel_name
            FROM hotel_bookings hb
            LEFT JOIN users u ON hb.user_id = u.id
            LEFT JOIN hotels h ON hb.hotel_id = h.id
            ORDER BY hb.booking_date DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching hotel bookings: " . $e->getMessage());
        return [];
    }
}

function getAllTicketBookings() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT t.*,
                   u.username,
                   u.email,
                   u.full_name,
                   m.team1,
                   m.team2,
                   m.match_date
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN matches m ON t.match_id = m.id
            ORDER BY t.booking_date DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching ticket bookings: " . $e->getMessage());
        return [];
    }
}



function getAllHotels() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT * FROM hotels 
            ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching hotels: " . $e->getMessage());
        return [];
    }
}


function getAllTransports() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT * FROM transport 
            WHERE deleted_at IS NULL 
            ORDER BY vehicle_type ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching transports: " . $e->getMessage());
        return [];
    }
}

// Get data
$transport_bookings = getAllTransportBookings();
$hotel_bookings = getAllHotelBookings();
$ticket_bookings = getAllTicketBookings();
$users = getAllUsers();
$hotels = getAllHotels();
$matches = getAllMatches();
$transports = getAllTransports();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                handleAddAction($table, $_POST);
                break;
                
            case 'edit':
                handleEditAction($table, $_POST);
                break;
                
            case 'delete':
                handleDeleteAction($table, $_POST['id']);
                break;
                
            case 'update_status':
                handleUpdateStatus($table, $_POST['id'], $_POST['status']);
                break;
        }
    } catch (Exception $e) {
        setAlert('error', "Error: " . $e->getMessage());
    }
}

function handleAddAction($table, $data) {
    global $pdo;
    
    switch ($table) {
        case 'hotels':
            $stmt = $pdo->prepare("
                INSERT INTO hotels (name, location, description, price_per_night, available_rooms, rating, amenities, distance_to_stadium) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['location'],
                $data['description'],
                $data['price_per_night'],
                $data['available_rooms'],
                $data['rating'],
                $data['amenities'],
                $data['distance_to_stadium']
            ]);
            setAlert('success', "Hotel added successfully!");
            break;
            
        case 'matches':
            $stmt = $pdo->prepare("
                INSERT INTO matches (team1, team2, competition, stadium, match_date, match_time, category, base_price, available_tickets) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['team1'],
                $data['team2'],
                $data['competition'],
                $data['stadium'],
                $data['match_date'],
                $data['match_time'],
                $data['category'],
                $data['base_price'],
                $data['available_tickets']
            ]);
            setAlert('success', "Match added successfully!");
            break;
            
        case 'transport':
            $stmt = $pdo->prepare("
                INSERT INTO transport (vehicle_name, vehicle_type, capacity, driver_name, driver_contact, registration_no, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['vehicle_name'],
                $data['vehicle_type'],
                $data['capacity'],
                $data['driver_name'],
                $data['driver_contact'],
                $data['registration_no'],
                $data['status']
            ]);
            setAlert('success', "Transport vehicle added successfully!");
            break;
    }
}

function handleEditAction($table, $data) {
    global $pdo;
    $id = $data['id'];
    
    switch ($table) {
        case 'hotels':
            $stmt = $pdo->prepare("
                UPDATE hotels SET 
                name = ?, location = ?, description = ?, price_per_night = ?, 
                available_rooms = ?, rating = ?, amenities = ?, distance_to_stadium = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['location'],
                $data['description'],
                $data['price_per_night'],
                $data['available_rooms'],
                $data['rating'],
                $data['amenities'],
                $data['distance_to_stadium'],
                $id
            ]);
            setAlert('success', "Hotel updated successfully!");
            break;
            
        case 'matches':
            $stmt = $pdo->prepare("
                UPDATE matches SET 
                team1 = ?, team2 = ?, competition = ?, stadium = ?, 
                match_date = ?, match_time = ?, category = ?, 
                base_price = ?, available_tickets = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['team1'],
                $data['team2'],
                $data['competition'],
                $data['stadium'],
                $data['match_date'],
                $data['match_time'],
                $data['category'],
                $data['base_price'],
                $data['available_tickets'],
                $id
            ]);
            setAlert('success', "Match updated successfully!");
            break;
            
        case 'transport':
            $stmt = $pdo->prepare("
                UPDATE transport SET 
                vehicle_name = ?, vehicle_type = ?, capacity = ?, 
                driver_name = ?, driver_contact = ?, registration_no = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['vehicle_name'],
                $data['vehicle_type'],
                $data['capacity'],
                $data['driver_name'],
                $data['driver_contact'],
                $data['registration_no'],
                $data['status'],
                $id
            ]);
            setAlert('success', "Transport vehicle updated successfully!");
            break;
            
        case 'users':
            $stmt = $pdo->prepare("
                UPDATE users SET 
                username = ?, email = ?, full_name = ?, phone = ?, 
                account_status = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $data['full_name'],
                $data['phone'],
                $data['account_status'],
                $id
            ]);
            setAlert('success', "User updated successfully!");
            break;
    }
}

function handleDeleteAction($table, $id) {
    global $pdo;
    
    switch ($table) {
        case 'hotels':
            $stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Hotel deleted successfully!");
            break;
            
        case 'matches':
            $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Match deleted successfully!");
            break;
            
        case 'transport':
            $stmt = $pdo->prepare("UPDATE transport SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Transport vehicle deleted successfully!");
            break;
            
        case 'transport_bookings':
            $stmt = $pdo->prepare("DELETE FROM transport_bookings WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Transport booking deleted successfully!");
            break;
            
        case 'hotel_bookings':
            $stmt = $pdo->prepare("DELETE FROM hotel_bookings WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Hotel booking deleted successfully!");
            break;
            
        case 'tickets':
            $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
            $stmt->execute([$id]);
            setAlert('success', "Ticket booking deleted successfully!");
            break;
    }
}

function handleUpdateStatus($table, $id, $status) {
    global $pdo;
    
    switch ($table) {
        case 'transport_bookings':
            $stmt = $pdo->prepare("UPDATE transport_bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            setAlert('success', "Transport booking status updated!");
            break;
            
        case 'hotel_bookings':
            $stmt = $pdo->prepare("UPDATE hotel_bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            setAlert('success', "Hotel booking status updated!");
            break;
            
        case 'tickets':
            $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            setAlert('success', "Ticket booking status updated!");
            break;
    }
}

// Get updated data after actions
$transport_bookings = getAllTransportBookings();
$hotel_bookings = getAllHotelBookings();
$ticket_bookings = getAllTicketBookings();
$users = getAllUsers();
$hotels = getAllHotels();
$matches = getAllMatches();
$transports = getAllTransports();
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
       <link rel="stylesheet" href="css/admin-style.css">
    
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
                <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin.php" class="active"><i class="fas fa-cog"></i> Admin</a></li>
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
            <h1>Admin Dashboard</h1>
            <p>Manage all bookings, users, and system configurations</p>
        </div>
    </section>

    <!-- ===== Main Content ===== -->
    <div class="admin-container">
        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--primary);">
                    <i class="fas fa-bus"></i>
                </div>
                <span class="stat-number"><?php echo count($transport_bookings); ?></span>
                <span class="stat-label">Transport Bookings</span>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--accent);">
                    <i class="fas fa-hotel"></i>
                </div>
                <span class="stat-number"><?php echo count($hotel_bookings); ?></span>
                <span class="stat-label">Hotel Bookings</span>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span class="stat-number"><?php echo count($ticket_bookings); ?></span>
                <span class="stat-label">Ticket Bookings</span>
            </div>
            
       
        </div>

        <!-- Tab Navigation -->
        <div class="admin-tabs">
            <div class="tab-nav">
                <button class="tab-btn active" onclick="showTab('transport')">
                    <i class="fas fa-bus"></i> Transport
                </button>
                <button class="tab-btn" onclick="showTab('hotels')">
                    <i class="fas fa-hotel"></i> Hotels
                </button>
                <button class="tab-btn" onclick="showTab('matches')">
                    <i class="fas fa-futbol"></i> Matches
                </button>
                <button class="tab-btn" onclick="showTab('bookings')">
                    <i class="fas fa-bookmark"></i> All Bookings
                </button>
                <button class="tab-btn" onclick="showTab('users')">
                    <i class="fas fa-users"></i> Users
                </button>
            </div>

            <!-- Transport Management Tab -->
            <div id="transport-tab" class="tab-content active">
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-bus"></i> Transport Vehicles
                        </h3>
                        <button class="add-btn" onclick="showAddModal('transport')">
                            <i class="fas fa-plus"></i> Add Vehicle
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vehicle Name</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Driver</th>
                                <th>Contact</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transports as $transport): ?>
                            <tr>
                                <td>#<?php echo $transport['id']; ?></td>
                                <td><?php echo htmlspecialchars($transport['vehicle_name']); ?></td>
                                <td><?php echo ucfirst($transport['vehicle_type']); ?></td>
                                <td><?php echo $transport['capacity']; ?></td>
                                <td><?php echo htmlspecialchars($transport['driver_name']); ?></td>
                                <td><?php echo htmlspecialchars($transport['driver_contact']); ?></td>
                                <td><?php echo htmlspecialchars($transport['registration_no']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $transport['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $transport['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-edit" onclick="showEditModal('transport', <?php echo $transport['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('transport', <?php echo $transport['id']; ?>, '<?php echo htmlspecialchars($transport['vehicle_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transports)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-bus fa-2x mb-3"></i>
                                    <p>No transport vehicles found</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Transport Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-shopping-cart"></i> Transport Bookings
                        </h3>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Route</th>
                                <th>Date & Time</th>
                                <th>Passengers</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transport_bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $booking['transport_type'])); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?></small>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></div>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($booking['departure_time'])); ?></small>
                                </td>
                                <td><?php echo $booking['quantity']; ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('transport_bookings', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('transport_bookings', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hotels Management Tab -->
            <div id="hotels-tab" class="tab-content">
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-hotel"></i> Hotels
                        </h3>
                        <button class="add-btn" onclick="showAddModal('hotels')">
                            <i class="fas fa-plus"></i> Add Hotel
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Price/Night</th>
                                <th>Rooms</th>
                                <th>Rating</th>
                                <th>Distance to Stadium</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotels as $hotel): ?>
                            <tr>
                                <td>#<?php echo $hotel['id']; ?></td>
                                <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                                <td>$<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                                <td><?php echo $hotel['available_rooms']; ?></td>
                                <td><?php echo $hotel['rating']; ?> <i class="fas fa-star text-warning"></i></td>
                                <td><?php echo htmlspecialchars($hotel['distance_to_stadium']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-edit" onclick="showEditModal('hotels', <?php echo $hotel['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('hotels', <?php echo $hotel['id']; ?>, '<?php echo htmlspecialchars($hotel['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Hotel Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-shopping-cart"></i> Hotel Bookings
                        </h3>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Hotel</th>
                                <th>Location</th>
                                <th>Check-in/out</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotel_bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                <td>
                                    <div><?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('M d', strtotime($booking['check_out'])); ?></div>
                                    <small class="text-muted"><?php echo $booking['nights']; ?> night(s)</small>
                                </td>
                                <td><?php echo $booking['guests']; ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('hotel_bookings', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('hotel_bookings', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Matches Management Tab -->
            <div id="matches-tab" class="tab-content">
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-futbol"></i> Matches
                        </h3>
                        <button class="add-btn" onclick="showAddModal('matches')">
                            <i class="fas fa-plus"></i> Add Match
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Teams</th>
                                <th>Competition</th>
                                <th>Stadium</th>
                                <th>Date & Time</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Tickets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $match): ?>
                            <tr>
                                <td>#<?php echo $match['id']; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($match['team1']); ?></div>
                                    <div class="text-muted">vs <?php echo htmlspecialchars($match['team2']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($match['competition']); ?></td>
                                <td><?php echo htmlspecialchars($match['stadium']); ?></td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($match['match_date'])); ?></div>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($match['match_time'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($match['category']); ?></td>
                                <td>$<?php echo number_format($match['base_price'], 2); ?></td>
                                <td><?php echo $match['available_tickets']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-edit" onclick="showEditModal('matches', <?php echo $match['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('matches', <?php echo $match['id']; ?>, '<?php echo htmlspecialchars($match['team1']); ?> vs <?php echo htmlspecialchars($match['team2']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Ticket Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-ticket-alt"></i> Ticket Bookings
                        </h3>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Match</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ticket_bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['team1']); ?> vs <?php echo htmlspecialchars($booking['team2']); ?></div>
                                    <small class="text-muted"><?php echo date('M d', strtotime($booking['match_date'])); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['match_date'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['venue']); ?></td>
                                <td><?php echo htmlspecialchars($booking['seat_category']); ?></td>
                                <td><?php echo $booking['quantity']; ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('tickets', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('tickets', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- All Bookings Tab -->
            <div id="bookings-tab" class="tab-content">
                <!-- Combined Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-bookmark"></i> All Bookings Summary
                        </h3>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Transport Bookings -->
                            <?php foreach ($transport_bookings as $booking): ?>
                            <tr>
                                <td>TRP#<?php echo $booking['id']; ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-bus"></i> Transport
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td>
                                    <small><?php echo ucfirst($booking['transport_type']); ?>: <?php echo htmlspecialchars($booking['from_location']); ?> → <?php echo htmlspecialchars($booking['to_location']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('transport_bookings', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('transport_bookings', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Hotel Bookings -->
                            <?php foreach ($hotel_bookings as $booking): ?>
                            <tr>
                                <td>HTL#<?php echo $booking['id']; ?></td>
                                <td>
                                    <span class="badge bg-accent" style="background: var(--accent);">
                                        <i class="fas fa-hotel"></i> Hotel
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($booking['hotel_name']); ?>, <?php echo htmlspecialchars($booking['location']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('hotel_bookings', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('hotel_bookings', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Ticket Bookings -->
                            <?php foreach ($ticket_bookings as $booking): ?>
                            <tr>
                                <td>TKT#<?php echo $booking['id']; ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-ticket-alt"></i> Ticket
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($booking['team1']); ?> vs <?php echo htmlspecialchars($booking['team2']); ?>, <?php echo htmlspecialchars($booking['venue']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['match_date'])); ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="showStatusModal('tickets', <?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn-sm btn-delete" onclick="confirmDelete('tickets', <?php echo $booking['id']; ?>, 'Booking #<?php echo $booking['id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Management Tab -->
    
    <!-- ===== Modals ===== -->
    
    <!-- Add Transport Modal -->
    <div class="modal fade" id="addTransportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addTransportForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-bus me-2"></i>Add Transport Vehicle</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="table" value="transport">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehicle Name *</label>
                                <input type="text" class="form-control" name="vehicle_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehicle Type *</label>
                                <select class="form-control" name="vehicle_type" required>
                                    <option value="metro">Metro</option>
                                    <option value="bus">Bus</option>
                                    <option value="train">Train</option>
                                    <option value="car_rental">Car Rental</option>
                                    <option value="taxi">Taxi</option>
                                    <option value="shuttle">Shuttle</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Capacity *</label>
                                <input type="number" class="form-control" name="capacity" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Driver Name *</label>
                                <input type="text" class="form-control" name="driver_name" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Driver Contact *</label>
                                <input type="tel" class="form-control" name="driver_contact" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration No *</label>
                                <input type="text" class="form-control" name="registration_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-control" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="in_use">In Use</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addHotelForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-hotel me-2"></i>Add Hotel</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="table" value="hotels">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hotel Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price per Night *</label>
                                <input type="number" class="form-control" name="price_per_night" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Available Rooms *</label>
                                <input type="number" class="form-control" name="available_rooms" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rating *</label>
                                <input type="number" class="form-control" name="rating" min="0" max="5" step="0.1" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amenities</label>
                                <input type="text" class="form-control" name="amenities" placeholder="Pool, WiFi, Gym, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Distance to Stadium</label>
                                <input type="text" class="form-control" name="distance_to_stadium" placeholder="e.g., 0.5 miles">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Match Modal -->
    <div class="modal fade" id="addMatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addMatchForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-futbol me-2"></i>Add Match</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="table" value="matches">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team 1 *</label>
                                <input type="text" class="form-control" name="team1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team 2 *</label>
                                <input type="text" class="form-control" name="team2" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Competition *</label>
                                <input type="text" class="form-control" name="competition" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stadium *</label>
                                <input type="text" class="form-control" name="stadium" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Match Date *</label>
                                <input type="date" class="form-control" name="match_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Match Time *</label>
                                <input type="time" class="form-control" name="match_time" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-control" name="category" required>
                                    <option value="Football">Football</option>
                                    <option value="Basketball">Basketball</option>
                                    <option value="Cricket">Cricket</option>
                                    <option value="Tennis">Tennis</option>
                                    <option value="Rugby">Rugby</option>
                                    <option value="Baseball">Baseball</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Base Price *</label>
                                <input type="number" class="form-control" name="base_price" step="0.01" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Available Tickets *</label>
                                <input type="number" class="form-control" name="available_tickets" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Match</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal (Dynamic) -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="editForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalTitle"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editModalBody">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="statusForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-sync me-2"></i>Update Status</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="table" id="statusTable">
                        <input type="hidden" name="id" id="statusId">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select class="form-control" name="status" id="statusSelect" required>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteForm">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="table" id="deleteTable">
                        <input type="hidden" name="id" id="deleteId">
                        
                        <p id="deleteMessage">Are you sure you want to delete this item?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Activate clicked button
            event.target.classList.add('active');
        }

        // Modal functions
        function showAddModal(table) {
            let modalId;
            switch(table) {
                case 'transport':
                    modalId = 'addTransportModal';
                    break;
                case 'hotels':
                    modalId = 'addHotelModal';
                    break;
                case 'matches':
                    modalId = 'addMatchModal';
                    break;
            }
            
            if (modalId) {
                const modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();
            }
        }

        function showEditModal(table, id) {
            // Fetch data via AJAX and populate modal
            fetch(`get_data.php?table=${table}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    let modalTitle = '';
                    let formContent = '';
                    
                    switch(table) {
                        case 'transport':
                            modalTitle = `<i class="fas fa-bus me-2"></i>Edit Transport Vehicle`;
                            formContent = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="${data.id}">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vehicle Name *</label>
                                        <input type="text" class="form-control" name="vehicle_name" value="${data.vehicle_name}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vehicle Type *</label>
                                        <select class="form-control" name="vehicle_type" required>
                                            <option value="metro" ${data.vehicle_type === 'metro' ? 'selected' : ''}>Metro</option>
                                            <option value="bus" ${data.vehicle_type === 'bus' ? 'selected' : ''}>Bus</option>
                                            <option value="train" ${data.vehicle_type === 'train' ? 'selected' : ''}>Train</option>
                                            <option value="car_rental" ${data.vehicle_type === 'car_rental' ? 'selected' : ''}>Car Rental</option>
                                            <option value="taxi" ${data.vehicle_type === 'taxi' ? 'selected' : ''}>Taxi</option>
                                            <option value="shuttle" ${data.vehicle_type === 'shuttle' ? 'selected' : ''}>Shuttle</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Capacity *</label>
                                        <input type="number" class="form-control" name="capacity" value="${data.capacity}" min="1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Driver Name *</label>
                                        <input type="text" class="form-control" name="driver_name" value="${data.driver_name}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Driver Contact *</label>
                                        <input type="tel" class="form-control" name="driver_contact" value="${data.driver_contact}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Registration No *</label>
                                        <input type="text" class="form-control" name="registration_no" value="${data.registration_no}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status *</label>
                                        <select class="form-control" name="status" required>
                                            <option value="available" ${data.status === 'available' ? 'selected' : ''}>Available</option>
                                            <option value="maintenance" ${data.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                            <option value="in_use" ${data.status === 'in_use' ? 'selected' : ''}>In Use</option>
                                        </select>
                                    </div>
                                </div>
                            `;
                            break;
                            
                        case 'hotels':
                            modalTitle = `<i class="fas fa-hotel me-2"></i>Edit Hotel`;
                            formContent = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="${data.id}">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hotel Name *</label>
                                        <input type="text" class="form-control" name="name" value="${data.name}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Location *</label>
                                        <input type="text" class="form-control" name="location" value="${data.location}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3">${data.description || ''}</textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Price per Night *</label>
                                        <input type="number" class="form-control" name="price_per_night" value="${data.price_per_night}" step="0.01" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Available Rooms *</label>
                                        <input type="number" class="form-control" name="available_rooms" value="${data.available_rooms}" min="0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Rating *</label>
                                        <input type="number" class="form-control" name="rating" value="${data.rating}" min="0" max="5" step="0.1" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Amenities</label>
                                        <input type="text" class="form-control" name="amenities" value="${data.amenities || ''}" placeholder="Pool, WiFi, Gym, etc.">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Distance to Stadium</label>
                                        <input type="text" class="form-control" name="distance_to_stadium" value="${data.distance_to_stadium || ''}" placeholder="e.g., 0.5 miles">
                                    </div>
                                </div>
                            `;
                            break;
                            
                        case 'matches':
                            modalTitle = `<i class="fas fa-futbol me-2"></i>Edit Match`;
                            formContent = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="${data.id}">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Team 1 *</label>
                                        <input type="text" class="form-control" name="team1" value="${data.team1}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Team 2 *</label>
                                        <input type="text" class="form-control" name="team2" value="${data.team2}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Competition *</label>
                                        <input type="text" class="form-control" name="competition" value="${data.competition}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Stadium *</label>
                                        <input type="text" class="form-control" name="stadium" value="${data.stadium}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Match Date *</label>
                                        <input type="date" class="form-control" name="match_date" value="${data.match_date}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Match Time *</label>
                                        <input type="time" class="form-control" name="match_time" value="${data.match_time}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category *</label>
                                        <select class="form-control" name="category" required>
                                            <option value="Football" ${data.category === 'Football' ? 'selected' : ''}>Football</option>
                                            <option value="Basketball" ${data.category === 'Basketball' ? 'selected' : ''}>Basketball</option>
                                            <option value="Cricket" ${data.category === 'Cricket' ? 'selected' : ''}>Cricket</option>
                                            <option value="Tennis" ${data.category === 'Tennis' ? 'selected' : ''}>Tennis</option>
                                            <option value="Rugby" ${data.category === 'Rugby' ? 'selected' : ''}>Rugby</option>
                                            <option value="Baseball" ${data.category === 'Baseball' ? 'selected' : ''}>Baseball</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Base Price *</label>
                                        <input type="number" class="form-control" name="base_price" value="${data.base_price}" step="0.01" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Available Tickets *</label>
                                        <input type="number" class="form-control" name="available_tickets" value="${data.available_tickets}" min="0" required>
                                    </div>
                                </div>
                            `;
                            break;
                            
                        case 'users':
                            modalTitle = `<i class="fas fa-user me-2"></i>Edit User`;
                            formContent = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="${data.id}">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username *</label>
                                        <input type="text" class="form-control" name="username" value="${data.username}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" value="${data.email}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" value="${data.full_name || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="${data.phone || ''}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Account Status *</label>
                                    <select class="form-control" name="account_status" required>
                                        <option value="active" ${data.account_status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="suspended" ${data.account_status === 'suspended' ? 'selected' : ''}>Suspended</option>
                                        <option value="pending" ${data.account_status === 'pending' ? 'selected' : ''}>Pending</option>
                                    </select>
                                </div>
                            `;
                            break;
                    }
                    
                    document.getElementById('editModalTitle').innerHTML = modalTitle;
                    document.getElementById('editModalBody').innerHTML = formContent;
                    
                    const modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    alert('Error loading data. Please try again.');
                });
        }

        function showStatusModal(table, id, currentStatus) {
            document.getElementById('statusTable').value = table;
            document.getElementById('statusId').value = id;
            document.getElementById('statusSelect').value = currentStatus;
            
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        function confirmDelete(table, id, name) {
            document.getElementById('deleteTable').value = table;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"?`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.querySelector('.mobile-menu');
            const navLinks = document.querySelector('.nav-links');
            
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                });
            }
            
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar')) {
                    navLinks.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>