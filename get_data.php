<?php
// get_data.php - Fetch data for edit modals
require_once 'config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['username'] ?? '') !== 'admin') {
    http_response_code(403);
    exit;
}

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$table || !$id) {
    http_response_code(400);
    exit;
}

try {
    switch ($table) {
        case 'transport':
            $stmt = $pdo->prepare("SELECT * FROM transport WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            break;
            
        case 'hotels':
            $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            break;
            
        case 'matches':
            $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            break;
            
        case 'users':
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            break;
            
        default:
            http_response_code(404);
            exit;
    }
    
    if ($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        http_response_code(404);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error fetching data: " . $e->getMessage());
}
?>