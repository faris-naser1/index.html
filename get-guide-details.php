<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid guide ID']);
    exit();
}

$guide_id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM tour_guides WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guide_id]);
    $guide = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($guide) {
        echo json_encode($guide);
    } else {
        echo json_encode(['error' => 'Guide not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>