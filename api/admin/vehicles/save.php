<?php
session_start();
require_once '../../../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? 'save';

if ($action === 'delete') {
    // Delete vehicle
    $vehicleId = $_POST['vehicle_id'] ?? 0;
    
    if (empty($vehicleId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Vehicle ID is required']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $vehicleId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete vehicle']);
    }
    
    $stmt->close();
    exit;
}

// Create or update vehicle
$vehicleId = $_POST['vehicle_id'] ?? null;
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$baseFare = $_POST['base_fare'] ?? 0;
$ratePerKm = $_POST['rate_per_km'] ?? 0;
$availability = isset($_POST['availability']) ? 1 : 0;
$isPremium = isset($_POST['is_premium']) ? 1 : 0;
$imageUrl = $_POST['image_url'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($name) || empty($category)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and category are required']);
    exit;
}

if ($vehicleId) {
    // Update existing vehicle
    $stmt = $conn->prepare("UPDATE vehicles SET name = ?, category = ?, base_fare = ?, rate_per_km = ?, availability = ?, is_premium = ?, image_url = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssddiissi", $name, $category, $baseFare, $ratePerKm, $availability, $isPremium, $imageUrl, $description, $vehicleId);
} else {
    // Create new vehicle
    $stmt = $conn->prepare("INSERT INTO vehicles (name, category, base_fare, rate_per_km, availability, is_premium, image_url, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddiiss", $name, $category, $baseFare, $ratePerKm, $availability, $isPremium, $imageUrl, $description);
}

if ($stmt->execute()) {
    $message = $vehicleId ? 'Vehicle updated successfully' : 'Vehicle created successfully';
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save vehicle']);
}

$stmt->close();
?>

