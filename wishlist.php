<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/currency.php';

requireLogin();

$userId = getUserId();

// Handle toggle action
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $vehicleId = (int)$_GET['id'];
    
    // Check if already in wishlist
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND vehicle_id = ?");
    if ($stmt !== false) {
        $stmt->bind_param("ii", $userId, $vehicleId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Remove from wishlist
                $stmt->close();
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND vehicle_id = ?");
                if ($stmt !== false) {
                    $stmt->bind_param("ii", $userId, $vehicleId);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Add to wishlist
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO wishlist (user_id, vehicle_id) VALUES (?, ?)");
                if ($stmt !== false) {
                    $stmt->bind_param("ii", $userId, $vehicleId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } else {
            $stmt->close();
        }
    }
    
    // Redirect back to previous page or index
    $redirect = $_GET['redirect'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$pageTitle = 'My Wishlist - RideHub';
include 'includes/header.php';

// Get wishlist vehicles
$query = "SELECT v.* 
          FROM wishlist w 
          JOIN vehicles v ON w.vehicle_id = v.id 
          WHERE w.user_id = ? 
          ORDER BY w.created_at DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    $vehicles = [];
    error_log("SQL Prepare Error: " . $conn->error);
} else {
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $vehicles = [];
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    } else {
        $vehicles = [];
        error_log("SQL Execute Error: " . $stmt->error);
    }
    $stmt->close();
}

// Map vehicle names to images
function getVehicleImage($vehicleName, $category, $imageUrl) {
    if (!empty($imageUrl) && file_exists($imageUrl)) {
        return $imageUrl;
    }
    
    $imageMap = [
        'honda' => 'public/honda-motorcycle.jpg',
        'yamaha' => 'public/yamaha-motorcycle.jpg',
        'kawasaki' => 'public/kawasaki-motorcycle.jpg',
        'vespa' => 'public/vespa-scooter.jpg',
        'piaggio' => 'public/piaggio-scooter.jpg',
        'activa' => 'public/honda-scooter.jpg',
        'bmw' => 'public/bmw-car.jpg',
        'mercedes' => 'public/mercedes-premium.jpg',
        'porsche' => 'public/porsche-911.jpg',
        'tesla' => 'public/tesla-model-s.jpg',
        'toyota' => 'public/toyota-camry.jpg',
    ];
    
    $nameLower = strtolower($vehicleName);
    foreach ($imageMap as $key => $image) {
        if (strpos($nameLower, $key) !== false) {
            return $image;
        }
    }
    
    switch (strtolower($category)) {
        case 'bikes': return 'public/honda-motorcycle.jpg';
        case 'scooters': return 'public/vespa-scooter.jpg';
        case 'cars': return 'public/toyota-camry.jpg';
        default: return 'public/placeholder.jpg';
    }
}
?>

<style>
.wishlist-section {
    padding: 60px 20px;
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.wishlist-section h1 {
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #333;
    text-align: center;
    animation: slideDown 0.6s ease-out;
}

.empty-message {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    animation: scaleIn 0.6s ease-out;
}

.empty-message p {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 30px;
}

.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.vehicle-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: slideUp 0.6s ease-out both;
    position: relative;
}

.vehicle-card:nth-child(1) { animation-delay: 0.1s; }
.vehicle-card:nth-child(2) { animation-delay: 0.2s; }
.vehicle-card:nth-child(3) { animation-delay: 0.3s; }
.vehicle-card:nth-child(4) { animation-delay: 0.4s; }
.vehicle-card:nth-child(5) { animation-delay: 0.5s; }
.vehicle-card:nth-child(6) { animation-delay: 0.6s; }

.vehicle-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.vehicle-image-container {
    position: relative;
    width: 100%;
    height: 240px;
    overflow: hidden;
    background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
}

.vehicle-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.vehicle-card:hover .vehicle-image {
    transform: scale(1.1);
}

.vehicle-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 2;
}

.vehicle-wishlist-btn {
    position: absolute;
    top: 16px;
    left: 16px;
    background: #ff6b6b;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    text-decoration: none;
    z-index: 2;
    font-size: 1.3rem;
}

.vehicle-wishlist-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

.vehicle-card-body {
    padding: 28px;
}

.vehicle-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #333;
}

.vehicle-price {
    margin-bottom: 16px;
    display: flex;
    align-items: baseline;
    gap: 6px;
    flex-wrap: wrap;
}

.vehicle-price-amount {
    font-size: 1.9rem;
    font-weight: 800;
    color: #333;
}

.vehicle-price-unit {
    color: #666;
    font-size: 1rem;
}

.vehicle-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 24px;
}

.vehicle-book-btn {
    display: block;
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.vehicle-book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
    .vehicles-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="wishlist-section">
    <div class="container">
        <h1>My Wishlist</h1>
        
        <?php if (empty($vehicles)): ?>
            <div class="empty-message">
                <div style="font-size: 5rem; margin-bottom: 20px; animation: pulse 2s infinite;">❤️</div>
                <p>Your wishlist is empty.</p>
                <p style="font-size: 1rem; margin-top: 10px;">Start adding your favorite vehicles to your wishlist!</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 30px; display: inline-block; padding: 14px 32px; border-radius: 30px;">Browse Vehicles</a>
            </div>
        <?php else: ?>
            <div class="vehicles-grid">
                <?php foreach ($vehicles as $vehicle): ?>
                    <?php 
                    $imageUrl = getVehicleImage($vehicle['name'], $vehicle['category'], $vehicle['image_url'] ?? '');
                    ?>
                    <div class="vehicle-card">
                        <div class="vehicle-image-container">
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" class="vehicle-image" onerror="this.src='public/placeholder.jpg'">
                            <?php if ($vehicle['is_premium']): ?>
                                <div class="vehicle-badge">⭐ Premium</div>
                            <?php endif; ?>
                            <a href="wishlist.php?action=toggle&id=<?php echo $vehicle['id']; ?>&redirect=wishlist.php" class="vehicle-wishlist-btn active" title="Remove from wishlist">❤️</a>
                        </div>
                        <div class="vehicle-card-body">
                            <h3 class="vehicle-name"><?php echo htmlspecialchars($vehicle['name']); ?></h3>
                            <p style="color: #667eea; font-size: 0.85rem; font-weight: 700; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo htmlspecialchars($vehicle['category']); ?></p>
                            <div class="vehicle-price">
                                <span class="vehicle-price-amount"><?php echo formatCurrency($vehicle['base_fare']); ?></span>
                                <span class="vehicle-price-unit">/day</span>
                                <span style="color: #999; font-size: 0.85rem; margin-left: auto; background: #f8f9fa; padding: 4px 10px; border-radius: 12px;">+ <?php echo formatCurrency($vehicle['rate_per_km']); ?>/km</span>
                            </div>
                            <p class="vehicle-description"><?php echo htmlspecialchars($vehicle['description'] ?? 'Perfect ride for your journey'); ?></p>
                            <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="vehicle-book-btn">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
