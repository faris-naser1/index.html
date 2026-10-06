<?php
// insert_sample_transports.php
require_once 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Sample transport vehicles
$sample_transports = [
    [
        'vehicle_name' => 'City Metro Express',
        'vehicle_type' => 'metro',
        'capacity' => 200,
        'driver_name' => 'Metro Operator',
        'driver_contact' => '+1234567890',
        'registration_no' => 'MET-001',
        'price_per_hour' => 500.00,
        'status' => 'available'
    ],
    [
        'vehicle_name' => 'Stadium Shuttle Bus',
        'vehicle_type' => 'bus',
        'capacity' => 50,
        'driver_name' => 'John Smith',
        'driver_contact' => '+1234567891',
        'registration_no' => 'BUS-001',
        'price_per_hour' => 200.00,
        'status' => 'available'
    ],
    [
        'vehicle_name' => 'VIP Limousine',
        'vehicle_type' => 'car_rental',
        'capacity' => 8,
        'driver_name' => 'Michael Brown',
        'driver_contact' => '+1234567892',
        'registration_no' => 'CAR-001',
        'price_per_hour' => 100.00,
        'status' => 'available'
    ],
    [
        'vehicle_name' => 'Express Train',
        'vehicle_type' => 'train',
        'capacity' => 300,
        'driver_name' => 'Train Operator',
        'driver_contact' => '+1234567893',
        'registration_no' => 'TRAIN-001',
        'price_per_hour' => 800.00,
        'status' => 'available'
    ],
    [
        'vehicle_name' => 'Airport Taxi Service',
        'vehicle_type' => 'taxi',
        'capacity' => 4,
        'driver_name' => 'David Wilson',
        'driver_contact' => '+1234567894',
        'registration_no' => 'TAXI-001',
        'price_per_hour' => 80.00,
        'status' => 'available'
    ],
    [
        'vehicle_name' => 'Hotel Shuttle Van',
        'vehicle_type' => 'shuttle',
        'capacity' => 15,
        'driver_name' => 'Robert Johnson',
        'driver_contact' => '+1234567895',
        'registration_no' => 'SHUTTLE-001',
        'price_per_hour' => 150.00,
        'status' => 'available'
    ]
];

$inserted = 0;
$errors = [];

foreach ($sample_transports as $transport) {
    // Check if already exists
    $check_query = "SELECT id FROM transport WHERE registration_no = '{$transport['registration_no']}'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $query = "INSERT INTO transport (
                    vehicle_name, 
                    vehicle_type, 
                    capacity, 
                    driver_name, 
                    driver_contact, 
                    registration_no, 
                    price_per_hour,
                    status
                  ) VALUES (
                    '{$transport['vehicle_name']}',
                    '{$transport['vehicle_type']}',
                    {$transport['capacity']},
                    '{$transport['driver_name']}',
                    '{$transport['driver_contact']}',
                    '{$transport['registration_no']}',
                    {$transport['price_per_hour']},
                    '{$transport['status']}'
                  )";
        
        if (mysqli_query($conn, $query)) {
            $inserted++;
        } else {
            $errors[] = mysqli_error($conn);
        }
    }
}

$_SESSION['success_message'] = "Inserted $inserted sample transport vehicles. " . 
                               (count($errors) > 0 ? "Errors: " . implode(', ', $errors) : "");
header('Location: transport.php');
exit;
?>