

<?php
header("Content-Type: application/json");

// CHANGE DB LOGIN DETAILS BELOW
$host = "localhost:8080";
$user = "root";
$pass = "";
$db   = "vehicle_rental";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([]));
}

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

// SEARCH your table
$sql = "
    SELECT id, name, category, is_primium, base_fair, rate_per_km, availability, image_url, description
    FROM vehicles
    WHERE name LIKE '%$q%'
       OR category LIKE '%$q%'
       OR description LIKE '%$q%'
";

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);