<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userId = $_SESSION['user_id'];
$vehicleId = $_POST['vehicle_id'] ?? 0;

if (empty($vehicleId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Vehicle ID is required']);
    exit;
}

// Check if already in wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND vehicle_id = ?");
$stmt->bind_param("ii", $userId, $vehicleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from wishlist
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND vehicle_id = ?");
    $stmt->bind_param("ii", $userId, $vehicleId);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Add to wishlist
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, vehicle_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $vehicleId);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'added']);
}

$stmt->close();
?>

