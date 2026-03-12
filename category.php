<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/currency.php';

$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
$userName = getUserName();

// Get category from URL
$category = $_GET['category'] ?? 'bikes';
$categoryMap = [
    'bikes' => 'Bikes',
    'scooters' => 'Scooters',
    'cars' => 'Cars',
    'premium' => 'Premium'
];

$dbCategory = $categoryMap[$category] ?? 'Bikes';
$categoryName = ucfirst($category);

// Get vehicles for this category
function getVehiclesByCategory($conn, $category) {
    $vehicles = [];
    $categoryMap = [
        'bikes' => 'Bikes',
        'scooters' => 'Scooters',
        'cars' => 'Cars',
        'premium' => 'Premium'
    ];
    $dbCategory = $categoryMap[$category] ?? 'Bikes';
    
    if ($category === 'premium') {
        $query = "SELECT * FROM vehicles WHERE availability = 1 AND is_premium = 1 ORDER BY base_fare ASC";
    } else {
        $query = "SELECT * FROM vehicles WHERE availability = 1 AND category = ? ORDER BY is_premium DESC, base_fare ASC";
    }
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("SQL Prepare Error: " . $conn->error);
        return $vehicles;
    }
    
    if ($category !== 'premium') {
        $stmt->bind_param("s", $dbCategory);
    }
    
    if (!$stmt->execute()) {
        error_log("SQL Execute Error: " . $stmt->error);
        $stmt->close();
        return $vehicles;
    }
    
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
    $stmt->close();
    
    return $vehicles;
}

$vehicles = getVehiclesByCategory($conn, $category);

// Get user wishlist if logged in
$wishlistIds = [];
if ($isLoggedIn) {
    $userId = getUserId();
    $stmt = $conn->prepare("SELECT vehicle_id FROM wishlist WHERE user_id = ?");
    if ($stmt !== false) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $wishlistIds[] = $row['vehicle_id'];
            }
        }
        $stmt->close();
    }
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

$pageTitle = $categoryName . ' - RideHub';
include 'includes/header.php';
?>

<style>
.category-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 20px 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.category-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}

.category-hero-content {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.category-hero h1 {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    margin-bottom: 20px;
    animation: slideDown 0.6s ease-out;
}

.category-hero p {
    font-size: 1.2rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
    animation: fadeIn 0.8s ease-out;
}

.category-breadcrumb {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
}

.category-breadcrumb a {
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s ease;
}

.category-breadcrumb a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.vehicles-listing {
    padding: 60px 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 400px);
}

.vehicles-container {
    max-width: 1400px;
    margin: 0 auto;
}

.vehicles-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.vehicles-header h2 {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.vehicles-count {
    color: #666;
    font-size: 1rem;
}

.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.vehicle-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: slideUp 0.6s ease-out both;
    position: relative;
    display: flex;
    flex-direction: column;
}

.vehicle-card:nth-child(1) { animation-delay: 0.1s; }
.vehicle-card:nth-child(2) { animation-delay: 0.2s; }
.vehicle-card:nth-child(3) { animation-delay: 0.3s; }
.vehicle-card:nth-child(4) { animation-delay: 0.4s; }
.vehicle-card:nth-child(5) { animation-delay: 0.5s; }
.vehicle-card:nth-child(6) { animation-delay: 0.6s; }

.vehicle-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.vehicle-image-container {
    position: relative;
    width: 100%;
    height: 280px;
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
    background: rgba(255, 255, 255, 0.95);
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
    z-index: 2;
}

.vehicle-wishlist-btn.active {
    background: #ff6b6b;
    color: white;
}

.vehicle-wishlist-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.vehicle-wishlist-btn svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.vehicle-card-body {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.vehicle-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #333;
    line-height: 1.3;
}

.vehicle-category {
    color: #667eea;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.vehicle-price {
    margin-bottom: 16px;
    display: flex;
    align-items: baseline;
    gap: 6px;
    flex-wrap: wrap;
}

.vehicle-price-amount {
    font-size: 2rem;
    font-weight: 800;
    color: #333;
}

.vehicle-price-unit {
    color: #666;
    font-size: 1rem;
}

.vehicle-rate {
    color: #999;
    font-size: 0.9rem;
    margin-left: auto;
    background: #f8f9fa;
    padding: 4px 10px;
    border-radius: 12px;
}

.vehicle-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.vehicle-features {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.vehicle-feature {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #666;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 20px;
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
    margin-top: auto;
}

.vehicle-book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state-icon {
    font-size: 5rem;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 16px;
}

.empty-state p {
    color: #666;
    font-size: 1rem;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .vehicles-grid {
        grid-template-columns: 1fr;
    }
    
    .vehicles-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<section class="category-hero">
    <div class="category-hero-content">
        <h1>
            <?php 
            $icons = [
                'bikes' => '🏍️',
                'scooters' => '🛵',
                'cars' => '🚗',
                'premium' => '⭐'
            ];
            echo ($icons[$category] ?? '🚗') . ' ' . $categoryName;
            ?>
        </h1>
        <p>Explore our premium collection of <?php echo strtolower($categoryName); ?>. Choose from a wide range of carefully maintained vehicles.</p>
    </div>
</section>

<div class="category-breadcrumb">
    <a href="index.php">Home</a>
    <span>/</span>
    <span><?php echo $categoryName; ?></span>
</div>

<section class="vehicles-listing">
    <div class="vehicles-container">
        <div class="vehicles-header">
            <div>
                <h2>Available <?php echo $categoryName; ?></h2>
                <p class="vehicles-count"><?php echo count($vehicles); ?> vehicles available</p>
            </div>
        </div>
        
        <?php if (empty($vehicles)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🚗</div>
                <h3>No Vehicles Available</h3>
                <p>We're updating our fleet in this category. Please check back soon!</p>
                <a href="index.php" class="vehicle-book-btn" style="display: inline-block; width: auto; padding: 14px 32px;">Back to Home</a>
            </div>
        <?php else: ?>
            <div class="vehicles-grid">
                <?php foreach ($vehicles as $index => $vehicle): ?>
                    <?php 
                    $imageUrl = getVehicleImage($vehicle['name'], $vehicle['category'], $vehicle['image_url'] ?? '');
                    $isWishlisted = in_array($vehicle['id'], $wishlistIds);
                    ?>
                    <div class="vehicle-card">
                        <div class="vehicle-image-container">
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" class="vehicle-image" onerror="this.src='public/placeholder.jpg'">
                            <?php if ($vehicle['is_premium']): ?>
                                <div class="vehicle-badge">⭐ Premium</div>
                            <?php endif; ?>
                            <?php if ($isLoggedIn): ?>
                                <a href="wishlist.php?action=toggle&id=<?php echo $vehicle['id']; ?>&redirect=category.php?category=<?php echo $category; ?>" class="vehicle-wishlist-btn <?php echo $isWishlisted ? 'active' : ''; ?>" title="<?php echo $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-card-body">
                            <h3 class="vehicle-name"><?php echo htmlspecialchars($vehicle['name']); ?></h3>
                            <p class="vehicle-category"><?php echo htmlspecialchars($vehicle['category']); ?></p>
                            <div class="vehicle-price">
                                <span class="vehicle-price-amount"><?php echo formatCurrency($vehicle['base_fare']); ?></span>
                                <span class="vehicle-price-unit">/day</span>
                                <span class="vehicle-rate">+ <?php echo formatCurrency($vehicle['rate_per_km']); ?>/km</span>
                            </div>
                            <p class="vehicle-description"><?php echo htmlspecialchars($vehicle['description'] ?? 'Perfect ride for your journey'); ?></p>
                            <div class="vehicle-features">
                                <span class="vehicle-feature">
                                    <span>✅</span>
                                    <span>Available</span>
                                </span>
                                <?php if ($vehicle['is_premium']): ?>
                                    <span class="vehicle-feature">
                                        <span>⭐</span>
                                        <span>Premium</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isLoggedIn): ?>
                                <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="vehicle-book-btn">Book Now</a>
                            <?php else: ?>
                                <a href="login.php" class="vehicle-book-btn">Login to Book</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

