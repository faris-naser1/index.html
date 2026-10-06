<?php
session_start();
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_transport'])) {

    $user_id = $_SESSION['user_id'];

    $transport_type  = $_POST['transport_type'];
    $departure_date  = $_POST['departure_date'];
    $departure_time  = $_POST['departure_time'];
    $from_location   = trim($_POST['from_location']);
    $to_location     = trim($_POST['to_location']);
    $quantity        = (int) $_POST['quantity'];
    $price           = (float) $_POST['price'];

    // Server-side total calculation (IMPORTANT)
    $total_amount = $price * $quantity;

    // Estimated arrival (minutes-based, safe)
    $hours_map = [
        'metro' => 0.5,
        'bus' => 1,
        'train' => 2,
        'car_rental' => 3,
        'taxi' => 1,
        'shuttle' => 1.5
    ];

    $minutes = (int)(($hours_map[$transport_type] ?? 1) * 60);
    $estimated_arrival = date(
        'H:i',
        strtotime($departure_time . " +{$minutes} minutes")
    );

    // Prepared statement (SAFE)
    $stmt = $conn->prepare("
        INSERT INTO transport_bookings
        (user_id, transport_type, departure_date, departure_time, estimated_arrival,
         from_location, to_location, quantity, price_per_ticket, total_amount, status, booking_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())
    ");

    $stmt->bind_param(
        "issssssidd",
        $user_id,
        $transport_type,
        $departure_date,
        $departure_time,
        $estimated_arrival,
        $from_location,
        $to_location,
        $quantity,
        $price,
        $total_amount
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Transport booked successfully.";
    } else {
        $_SESSION['error'] = "Booking failed: " . $stmt->error;
    }

    header("Location: transport.php");
    exit;
}

// Fetch recent bookings
$bookings = [];
$result = $conn->query("
    SELECT * FROM transport_bookings
    WHERE user_id = {$_SESSION['user_id']}
    ORDER BY booking_date DESC
    LIMIT 5
");
if ($result) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transport Booking</title>
</head>
<body>

<h1>Book Transport</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<form method="POST">
    <p>
        <label>Transport Type:</label><br>
        <select name="transport_type" required>
            <option value="">Select</option>
            <option value="metro">Metro</option>
            <option value="bus">Bus</option>
            <option value="train">Train</option>
            <option value="car_rental">Car Rental</option>
            <option value="taxi">Taxi</option>
            <option value="shuttle">Shuttle</option>
        </select>
    </p>

    <p>
        <label>Date:</label><br>
        <input type="date" name="departure_date" required min="<?php echo date('Y-m-d'); ?>">
    </p>

    <p>
        <label>Time:</label><br>
        <input type="time" name="departure_time" required>
    </p>

    <p>
        <label>From:</label><br>
        <input type="text" name="from_location" required>
    </p>

    <p>
        <label>To:</label><br>
        <input type="text" name="to_location" required>
    </p>

    <p>
        <label>Passengers:</label><br>
        <input type="number" name="quantity" min="1" max="10" value="1" required>
    </p>

    <p>
        <label>Price per ticket ($):</label><br>
        <input type="number" name="price" step="0.01" min="1" value="10" required>
    </p>

    <input type="hidden" name="book_transport" value="1">

    <button type="submit">Book Transport</button>
</form>

<hr>

<h2>Recent Bookings</h2>

<?php if (empty($bookings)): ?>
    <p>No bookings yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($bookings as $b): ?>
            <li>
                <strong><?php echo strtoupper($b['transport_type']); ?></strong> |
                <?php echo htmlspecialchars($b['from_location']); ?> →
                <?php echo htmlspecialchars($b['to_location']); ?> |
                <?php echo $b['departure_date']; ?> |
                <?php echo $b['departure_time']; ?> →
                <?php echo $b['estimated_arrival']; ?> |
                $<?php echo number_format($b['total_amount'], 2); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

</body>
</html>
