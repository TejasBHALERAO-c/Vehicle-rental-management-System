<?php
require_once 'config/db.php';
require_once 'includes/currency.php';
require_once 'includes/auth.php';
require_once 'index.php'; // for getVehicleImage()

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);

$sql = "
    SELECT * FROM vehicles
    WHERE name LIKE '%$q%'
    OR category LIKE '%$q%'
    OR description LIKE '%$q%'
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<p class='no-results'>No vehicles found</p>";
    exit;
}

$wishlistIds = [];
if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $wq = $conn->query("SELECT vehicle_id FROM wishlist WHERE user_id = $uid");
    while ($row = $wq->fetch_assoc()) {
        $wishlistIds[] = $row['vehicle_id'];
    }
}

while ($vehicle = $result->fetch_assoc()):
    $imageUrl = getVehicleImage($vehicle['name'], $vehicle['category'], $vehicle['image_url']);
    $isWishlisted = in_array($vehicle['id'], $wishlistIds);
?>

<div class="vehicle-card">
    <div class="vehicle-image">
        <img src="<?php echo $imageUrl; ?>" alt="<?php echo $vehicle['name']; ?>">
    </div>

    <div class="vehicle-info">
        <h3 class="vehicle-name"><?php echo $vehicle['name']; ?></h3>
        <p class="vehicle-category"><?php echo ucfirst($vehicle['category']); ?></p>

        <div class="vehicle-price">
            <span><?php echo formatCurrency($vehicle['base_fair']); ?></span>
            <span>/base</span>
        </div>

        <p class="vehicle-rate">₹<?php echo $vehicle['rate_per_km']; ?>/km</p>

        <?php if (isLoggedIn()): ?>
            <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="vehicle-book-btn">Book Now</a>
        <?php else: ?>
            <a href="login.php" class="vehicle-book-btn">Login to Book</a>
        <?php endif; ?>
    </div>
</div>

<?php endwhile; ?>
