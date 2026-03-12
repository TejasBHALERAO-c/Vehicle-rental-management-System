<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

$category = $_GET['category'] ?? 'all';
$isPremium = isset($_GET['premium']) ? (bool)$_GET['premium'] : null;

// Map category names to database format (Bikes, Scooters, Cars)
$categoryMap = [
    'Bikes' => 'Bikes',
    'Scooters' => 'Scooters',
    'Cars' => 'Cars',
    'Premium' => 'Premium',
    'bikes' => 'Bikes',
    'scooters' => 'Scooters',
    'cars' => 'Cars',
    'premium' => 'Premium',
    'Bike' => 'Bikes',
    'Scooter' => 'Scooters',
    'Car' => 'Cars'
];

// Normalize category
if (isset($categoryMap[$category])) {
    $dbCategory = $categoryMap[$category];
} else {
    $dbCategory = $category;
}

$query = "SELECT * FROM vehicles WHERE availability = 1";

if ($dbCategory === 'Premium' || $isPremium === true) {
    $query .= " AND is_premium = 1";
} elseif ($dbCategory !== 'all' && $dbCategory !== 'Premium') {
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE availability = 1 AND category = ?");
    if ($stmt !== false) {
        $stmt->bind_param("s", $dbCategory);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicles = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $vehicle = [
                    'id' => $row['id'] ?? 0,
                    'name' => $row['name'] ?? 'Unknown Vehicle',
                    'category' => $row['category'] ?? 'Unknown',
                    'base_fare' => floatval($row['base_fare'] ?? 0),
                    'rate_per_km' => floatval($row['rate_per_km'] ?? 0),
                    'image_url' => $row['image_url'] ?? 'public/placeholder.jpg',
                    'is_premium' => isset($row['is_premium']) ? (bool)$row['is_premium'] : false,
                    'description' => $row['description'] ?? '',
                    'availability' => isset($row['availability']) ? (bool)$row['availability'] : true
                ];
                $vehicles[] = $vehicle;
            }
        }
        $stmt->close();
        echo json_encode($vehicles);
        exit;
    }
}

$result = $conn->query($query);
$vehicles = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ensure all required fields are present
        $vehicle = [
            'id' => $row['id'] ?? 0,
            'name' => $row['name'] ?? $row['model'] ?? 'Unknown Vehicle',
            'model' => $row['model'] ?? '',
            'category' => $row['category'] ?? 'Unknown',
            'price' => $row['price_per_day'] ?? $row['base_fare'] ?? 0,
            'price_per_day' => $row['price_per_day'] ?? $row['base_fare'] ?? 0,
            'base_fare' => $row['base_fare'] ?? 0,
            'rate_per_km' => $row['rate_per_km'] ?? 0,
            'image' => $row['image_url'] ?? $row['image'] ?? '/placeholder.svg',
            'image_url' => $row['image_url'] ?? $row['image'] ?? '/placeholder.svg',
            'rating' => $row['rating'] ?? 4.5,
            'is_premium' => isset($row['is_premium']) ? (bool)$row['is_premium'] : false,
            'description' => $row['description'] ?? '',
            'availability' => isset($row['availability']) ? (bool)$row['availability'] : true
        ];
        $vehicles[] = $vehicle;
    }
}

echo json_encode($vehicles);
?>

