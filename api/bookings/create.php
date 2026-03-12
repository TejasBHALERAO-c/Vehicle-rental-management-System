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
$kmDistance = $_POST['km_distance'] ?? 0;
$pickupType = $_POST['pickup_type'] ?? 'pickup';
$deliveryAddress = $_POST['delivery_address'] ?? '';

if (empty($vehicleId) || empty($kmDistance)) {
    http_response_code(400);
    echo json_encode(['error' => 'Vehicle ID and distance are required']);
    exit;
}

// Get vehicle details
$stmt = $conn->prepare("SELECT base_fare, rate_per_km FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Vehicle not found']);
    $stmt->close();
    exit;
}

$vehicle = $result->fetch_assoc();
$stmt->close();

// Calculate estimated price
$estimatedPrice = $vehicle['base_fare'] + ($vehicle['rate_per_km'] * $kmDistance);

// Create booking
$stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_id, km_distance, estimated_price, pickup_type, delivery_address) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiidss", $userId, $vehicleId, $kmDistance, $estimatedPrice, $pickupType, $deliveryAddress);

if ($stmt->execute()) {
    $bookingId = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'estimated_price' => $estimatedPrice
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create booking']);
}

$stmt->close();
?>

