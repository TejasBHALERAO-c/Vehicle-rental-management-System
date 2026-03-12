<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/currency.php';

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection error. Please check your database configuration.");
}

$pageTitle = 'RideHub - Your Journey Starts Here';
include 'includes/header.php';

// Map vehicle names to images in public directory
function getVehicleImage($vehicleName, $category, $imageUrl) {
    // If image_url is set and exists, use it
    if (!empty($imageUrl) && file_exists($imageUrl)) {
        return $imageUrl;
    }
    
    // Map vehicle names to actual images in public directory
    $imageMap = [
        // Bikes
        'honda' => 'public/Honda_CB500F.jpg',
        'yamaha' => 'public/Yamaha_FZ-07.jpg',
        'kawasaki' => 'public/Kawasaki_Versys_650.jpg',
        'harley' => 'public/Harley_Davidson_Sportster.jpg',
        'suzuki' => 'public/Suzuki_GSX-R600.jpg',
        'royal enfield' => 'public/Royal_Enfield_Classic_350.jpg',
        
        // Scooters
        'vespa' => 'public/Vespa_LX_125.jpg',
        'piaggio' => 'public/Piaggio_Vespa_Primavera.jpg',
        'activa' => 'public/Honda_Activa_6G.jpg',
        'access' => 'public/Suzuki_Access_125.jpg',
        
        // Cars
        'bmw' => 'public/BMW_5_Series.jpg',
        'mercedes' => 'public/Mercedes_GLC.jpg',
        'porsche' => 'public/Porsche_911.jpg',
        'tesla' => 'public/Tesla_Model_S.jpg',
        'toyota' => 'public/Toyota_Camry.jpg',
        'honda city' => 'public/Honda_Accord.jpg',
        'fortuner' => 'public/Toyota_Fortuner.jpg',
        'audi' => 'public/Audi_A4.jpg',
        'hyundai' => 'public/Hyundai_Creta.jpg',
    ];
    
    $nameLower = strtolower($vehicleName);
    
    // Try to find matching image
    foreach ($imageMap as $key => $image) {
        if (strpos($nameLower, $key) !== false) {
            return $image;
        }
    }
    
    // Default images by category
    switch (strtolower($category)) {
        case 'bikes':
            return 'public/Honda_CB500F.jpg';
        case 'scooters':
            return 'public/Vespa_GTS_300.jpg';
        case 'cars':
            return 'public/Toyota_Camry.jpg';
        default:
            return 'public/placeholder.jpg';
    }
}

// Get vehicles by category
function getVehiclesByCategory($conn, $category) {
    $vehicles = [];
    
    if ($category === 'premium') {
        $query = "SELECT * FROM vehicles WHERE availability = 1 AND is_premium = 1";
        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
        } else {
            error_log("SQL Query Error: " . $conn->error);
        }
    } else {
        $categoryMap = [
            'bikes' => 'Bikes',
            'scooters' => 'Scooters',
            'cars' => 'Cars'
        ];
        $dbCategory = $categoryMap[$category] ?? 'Bikes';
        $query = "SELECT * FROM vehicles WHERE availability = 1 AND category = ? AND (is_premium IS NULL OR is_premium = 0)";
        
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            error_log("SQL Prepare Error: " . $conn->error);
            return $vehicles;
        }
        
        $stmt->bind_param("s", $dbCategory);
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
    }
    
    return $vehicles;
}

$bikes = getVehiclesByCategory($conn, 'bikes');
$scooters = getVehiclesByCategory($conn, 'scooters');
$cars = getVehiclesByCategory($conn, 'cars');
$premium = getVehiclesByCategory($conn, 'premium');

// Get user wishlist if logged in
$wishlistIds = [];
if ($isLoggedIn) {
    $userId = getUserId();
    $stmt = $conn->prepare("SELECT vehicle_id FROM wishlist WHERE user_id = ?");
    if ($stmt) {
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

function isInWishlist($vehicleId, $wishlistIds) {
    return in_array($vehicleId, $wishlistIds);
}

function renderVehicleCard($vehicle, $wishlistIds, $isLoggedIn) {
    $imageUrl = getVehicleImage($vehicle['name'], $vehicle['category'], $vehicle['image_url'] ?? '');
    $isWishlisted = isInWishlist($vehicle['id'], $wishlistIds);
    $wishlistClass = $isWishlisted ? 'active' : '';
    $badge = $vehicle['is_premium'] ? '<div class="vehicle-badge">⭐ Premium</div>' : '';
    
    return '
    <div class="vehicle-card">
        <div class="vehicle-image-container">
            <img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($vehicle['name']) . '" class="vehicle-image" onerror="this.src=\'public/placeholder.jpg\'">
            ' . $badge . '
            ' . ($isLoggedIn ? '<button class="vehicle-wishlist-btn ' . $wishlistClass . '" data-vehicle-id="' . $vehicle['id'] . '" title="' . ($isWishlisted ? 'Remove from wishlist' : 'Add to wishlist') . '">
                <svg class="wishlist-icon" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
            </button>' : '') . '
                </div>
        <div class="vehicle-card-body">
            <h3 class="vehicle-name">' . htmlspecialchars($vehicle['name']) . '</h3>
            <p class="vehicle-category">' . htmlspecialchars($vehicle['category']) . '</p>
            <div class="vehicle-price">
                <span class="vehicle-price-amount">' . formatCurrency($vehicle['base_fare']) . '</span>
                <span class="vehicle-price-unit">/day</span>
                <span class="vehicle-rate">+ ' . formatCurrency($vehicle['rate_per_km']) . '/km</span>
                </div>
            <p class="vehicle-description">' . htmlspecialchars($vehicle['description'] ?? 'Perfect ride for your journey') . '</p>
            ' . ($isLoggedIn ? '<a href="book.php?id=' . $vehicle['id'] . '" class="vehicle-book-btn">Book Now</a>' : '<a href="login.php" class="vehicle-book-btn">Login to Book</a>') . '
            </div>
    </div>';
}
?>

<style>
/* Enhanced Hero Section */
.hero {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 25%), url('public/tesla-model-s.jpg') center/cover;
    color: white;
    padding: 120px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    margin-bottom: 24px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
    animation: fadeInUp 0.8s ease;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2vw, 1.4rem);
    margin-bottom: 40px;
    opacity: 0.95;
    animation: fadeInUp 1s ease;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.hero-cta {
    display: inline-block;
    padding: 16px 48px;
    background: white;
    color: #667eea;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-top: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    animation: fadeInUp 1.2s ease;
}

.hero-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced Vehicle Cards */
.vehicles-section {
    padding: 100px 20px;
    background: #f8f9fa;
    position: relative;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: clamp(2rem, 4vw, 3rem);
    text-align: center;
    margin-bottom: 16px;
    color: #333;
    font-weight: 800;
    position: relative;
}

.section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 20px auto;
    border-radius: 2px;
}

.section-subtitle {
    text-align: center;
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.tabs-list {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.tabs-trigger {
    padding: 14px 32px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    text-decoration: none;
    color: #666;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.tabs-trigger:hover {
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

 .tabs-trigger.active {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     border-color: transparent;
     box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
 }

 .tabs-trigger {
     cursor: pointer;
 }

.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.vehicle-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    position: relative;
}

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
    background: white;
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

.vehicle-wishlist-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.vehicle-wishlist-btn.active {
    background: #ff6b6b;
}

.vehicle-wishlist-btn.active .wishlist-icon {
    fill: white;
}

.wishlist-icon {
    width: 22px;
    height: 22px;
    fill: #ccc;
    transition: fill 0.3s ease;
}

.vehicle-wishlist-btn:hover .wishlist-icon {
    fill: #ff6b6b;
}

.vehicle-wishlist-btn.active .wishlist-icon {
    fill: white;
}

.vehicle-card-body {
    padding: 28px;
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
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.vehicle-rate {
    color: #999;
    font-size: 0.85rem;
    margin-left: auto;
    background: #f8f9fa;
    padding: 4px 10px;
    border-radius: 12px;
}

.vehicle-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 24px;
    min-height: 48px;
     display: -webkit-box;
     -webkit-line-clamp: 2;
     line-clamp: 2;
     -webkit-box-orient: vertical;
     overflow: hidden;
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
    position: relative;
    overflow: hidden;
}

.vehicle-book-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.vehicle-book-btn:hover::before {
    left: 100%;
}

.vehicle-book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Features Section */
.features-section {
    padding: 100px 20px;
    background: white;
    position: relative;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 60px;
}

.feature-card {
    text-align: center;
    padding: 50px 30px;
    border-radius: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    transition: all 0.4s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-card:hover {
    border-color: #667eea;
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.feature-icon {
    font-size: 3.5rem;
    margin-bottom: 24px;
    display: inline-block;
    transition: transform 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1);
}

.feature-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0 0 16px;
    color: #333;
}

.feature-description {
    color: #666;
    line-height: 1.7;
    font-size: 1rem;
}

.text-center {
    text-align: center;
}

/* Stats Section */
.stats-section {
    padding: 80px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    text-align: center;
}

.stat-item {
    padding: 30px 20px;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 12px;
    display: block;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero {
        padding: 80px 20px;
    }
    
    .tabs-list {
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .tabs-trigger {
        width: 100%;
        max-width: 280px;
        justify-content: center;
    }
    
    .vehicles-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .vehicle-card-body {
        padding: 20px;
    }
    
    .feature-card {
        padding: 40px 20px;
    }
}

@media (max-width: 480px) {
    .hero {
        padding: 60px 20px;
    }
    
    .vehicles-section,
    .features-section {
        padding: 60px 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
}

/* Loading Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.loading {
    animation: pulse 2s infinite;
}

/* Wishlist Animation */
@keyframes heartBeat {
    0% { transform: scale(1); }
    25% { transform: scale(1.3); }
    50% { transform: scale(1); }
    75% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

 .vehicle-wishlist-btn.active {
     animation: heartBeat 0.6s ease;
 }

 /* Booking Modal */
 .booking-modal {
     display: none;
     position: fixed;
     top: 0;
     left: 0;
     width: 100%;
     height: 100%;
     background: rgba(0, 0, 0, 0.7);
     z-index: 1000;
     overflow-y: auto;
     animation: fadeIn 0.3s ease;
 }

 .booking-modal.active {
     display: flex;
     align-items: center;
     justify-content: center;
     padding: 20px;
 }

 @keyframes fadeIn {
     from { opacity: 0; }
     to { opacity: 1; }
 }

 .booking-modal-content {
     background: white;
     border-radius: 20px;
     max-width: 600px;
     width: 100%;
     max-height: 90vh;
     overflow-y: auto;
     position: relative;
     animation: slideUp 0.3s ease;
     box-shadow: 0 20px 60px rgba(0,0,0,0.3);
 }

 @keyframes slideUp {
     from {
         transform: translateY(50px);
         opacity: 0;
     }
     to {
         transform: translateY(0);
         opacity: 1;
     }
 }

 .booking-modal-header {
     padding: 30px;
     border-bottom: 2px solid #f0f0f0;
     display: flex;
     justify-content: space-between;
     align-items: center;
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     border-radius: 20px 20px 0 0;
 }

 .booking-modal-header h2 {
     margin: 0;
     font-size: 1.8rem;
     font-weight: 700;
 }

 .close-modal {
     background: rgba(255,255,255,0.2);
     border: none;
     color: white;
     font-size: 1.5rem;
     width: 40px;
     height: 40px;
     border-radius: 50%;
     cursor: pointer;
     transition: all 0.3s ease;
     display: flex;
     align-items: center;
     justify-content: center;
 }

 .close-modal:hover {
     background: rgba(255,255,255,0.3);
     transform: rotate(90deg);
 }

 .booking-modal-body {
     padding: 30px;
 }

 .login-prompt {
     text-align: center;
     padding: 40px 20px;
 }

 .login-prompt-icon {
     font-size: 4rem;
     margin-bottom: 20px;
 }

 .login-prompt h3 {
     font-size: 1.5rem;
     margin-bottom: 15px;
     color: #333;
 }

 .login-prompt p {
     color: #666;
     margin-bottom: 30px;
     line-height: 1.6;
 }

 .login-prompt-buttons {
     display: flex;
     gap: 15px;
     justify-content: center;
     flex-wrap: wrap;
 }

 .btn-login, .btn-register {
     padding: 14px 32px;
     border-radius: 30px;
     text-decoration: none;
     font-weight: 600;
     transition: all 0.3s ease;
     display: inline-block;
 }

 .btn-login {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
 }

 .btn-login:hover {
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
 }

 .btn-register {
     background: white;
     color: #667eea;
     border: 2px solid #667eea;
 }

 .btn-register:hover {
     background: #667eea;
     color: white;
 }

 .booking-form-group {
     margin-bottom: 25px;
 }

 .booking-form-group label {
     display: block;
     margin-bottom: 8px;
     font-weight: 600;
     color: #333;
     font-size: 0.95rem;
 }

 .booking-form-group select,
 .booking-form-group input,
 .booking-form-group textarea {
     width: 100%;
     padding: 14px;
     border: 2px solid #e0e0e0;
     border-radius: 10px;
     font-size: 1rem;
     transition: all 0.3s ease;
     box-sizing: border-box;
     font-family: inherit;
 }

 .booking-form-group textarea {
     resize: vertical;
     min-height: 80px;
 }

 .booking-form-group select:focus,
 .booking-form-group input:focus,
 .booking-form-group textarea:focus {
     outline: none;
     border-color: #667eea;
     box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
 }

 .booking-summary {
     background: #f8f9fa;
     padding: 20px;
     border-radius: 12px;
     margin-bottom: 25px;
 }

 .booking-summary-item {
     display: flex;
     justify-content: space-between;
     margin-bottom: 12px;
     font-size: 0.95rem;
 }

 .booking-summary-item:last-child {
     margin-bottom: 0;
     padding-top: 12px;
     border-top: 2px solid #e0e0e0;
     font-weight: 700;
     font-size: 1.1rem;
     color: #667eea;
 }

 .booking-submit-btn {
     width: 100%;
     padding: 16px;
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     border: none;
     border-radius: 12px;
     font-size: 1.1rem;
     font-weight: 700;
     cursor: pointer;
     transition: all 0.3s ease;
 }

 .booking-submit-btn:hover {
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
 }

 .booking-submit-btn:disabled {
     opacity: 0.6;
     cursor: not-allowed;
     transform: none;
 }

 /* Enhanced Animations */
 .vehicle-card {
     animation: slideUp 0.6s ease-out both;
 }

 .vehicle-card:nth-child(1) { animation-delay: 0.1s; }
 .vehicle-card:nth-child(2) { animation-delay: 0.2s; }
 .vehicle-card:nth-child(3) { animation-delay: 0.3s; }
 .vehicle-card:nth-child(4) { animation-delay: 0.4s; }
 .vehicle-card:nth-child(5) { animation-delay: 0.5s; }
 .vehicle-card:nth-child(6) { animation-delay: 0.6s; }

 /* Smooth Scroll */
 html {
     scroll-behavior: smooth;
 }

 /* Loading States */
 .loading-overlay {
     position: fixed;
     top: 0;
     left: 0;
     width: 100%;
     height: 100%;
     background: rgba(255, 255, 255, 0.9);
     display: flex;
     align-items: center;
     justify-content: center;
     z-index: 9999;
     opacity: 0;
     pointer-events: none;
     transition: opacity 0.3s ease;
 }

 .loading-overlay.active {
     opacity: 1;
     pointer-events: all;
 }
 </style>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
            <div class="hero-content">
                    <h1 class="hero-title">Your Journey Starts Here</h1>
                <p class="hero-subtitle">Browse premium vehicles, compare prices, and book the perfect ride for your next adventure. Experience the freedom of the open road with RideHub.</p>
                <?php if (!$isLoggedIn): ?>
                    <a href="register.php" class="hero-cta">
                        Start Your Journey
                    </a>
                <?php else: ?>
                    <a href="#vehicles" class="hero-cta">
                        Explore Vehicles
                    </a>
                <?php endif; ?>
                            </div>
                            </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Happy Customers</span>
                            </div>
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Vehicles</span>
                            </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Support</span>
                            </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vehicle Grid Section -->
        <section id="vehicles" class="vehicles-section">
            <div class="container">
            <h2 class="section-title">Our Premium Fleet</h2>
            <p class="section-subtitle">Choose from our diverse range of carefully maintained vehicles, each selected for comfort, performance, and reliability.</p>
                
                <div class="tabs">
                    <div class="tabs-list">
                    <a href="category.php?category=bikes" class="tabs-trigger category-tab <?php echo (!isset($_GET['category']) || $_GET['category'] === 'bikes') ? 'active' : ''; ?>">
                        🏍️ Motorcycles
                    </a>
                    <a href="category.php?category=scooters" class="tabs-trigger category-tab <?php echo (isset($_GET['category']) && $_GET['category'] === 'scooters') ? 'active' : ''; ?>">
                        🛵 Scooters
                    </a>
                    <a href="category.php?category=cars" class="tabs-trigger category-tab <?php echo (isset($_GET['category']) && $_GET['category'] === 'cars') ? 'active' : ''; ?>">
                        🚗 Cars
                    </a>
                    <a href="category.php?category=premium" class="tabs-trigger category-tab <?php echo (isset($_GET['category']) && $_GET['category'] === 'premium') ? 'active' : ''; ?>">
                        ⭐ Premium Collection
                    </a>
                    </div>
                    
                <div class="tabs-content">
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 4rem; margin-bottom: 20px;">🚗</div>
                        <h3 style="font-size: 1.8rem; color: #333; margin-bottom: 16px;">Explore Our Vehicle Categories</h3>
                        <p style="color: #666; max-width: 600px; margin: 0 auto 40px; line-height: 1.6; font-size: 1.1rem;">
                            Click on any category above to view all available vehicles with detailed information, images, and pricing. Each category page displays vehicles in an e-commerce style layout.
                        </p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; max-width: 800px; margin: 0 auto;">
                            <a href="category.php?category=bikes" class="category-card-link" style="padding: 20px; background: white; border-radius: 15px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; display: block;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">🏍️</div>
                                <h4 style="color: #333; margin: 0;">Motorcycles</h4>
                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0 0;">View all bikes</p>
                            </a>
                            <a href="category.php?category=scooters" class="category-card-link" style="padding: 20px; background: white; border-radius: 15px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; display: block;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">🛵</div>
                                <h4 style="color: #333; margin: 0;">Scooters</h4>
                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0 0;">View all scooters</p>
                            </a>
                            <a href="category.php?category=cars" class="category-card-link" style="padding: 20px; background: white; border-radius: 15px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; display: block;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">🚗</div>
                                <h4 style="color: #333; margin: 0;">Cars</h4>
                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0 0;">View all cars</p>
                            </a>
                            <a href="category.php?category=premium" class="category-card-link" style="padding: 20px; background: white; border-radius: 15px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; display: block;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">⭐</div>
                                <h4 style="color: #333; margin: 0;">Premium</h4>
                                <p style="color: #666; font-size: 0.9rem; margin: 5px 0 0;">View premium vehicles</p>
                            </a>
                        </div>
                        <style>
                        .category-card-link:hover {
                            transform: translateY(-5px);
                            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                        }
                        </style>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title text-center">Why Choose RideHub?</h2>
            <p class="section-subtitle">Experience the difference with our premium vehicle rental service</p>
                
                <div class="features-grid">
                <div class="feature-card stagger-item">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Instant Booking</h3>
                    <p class="feature-description">Book your perfect vehicle in minutes with our streamlined, user-friendly booking platform.</p>
                    </div>
                    
                <div class="feature-card stagger-item">
                    <div class="feature-icon">🛡️</div>
                    <h3 class="feature-title">Fully Insured</h3>
                    <p class="feature-description">All vehicles come with comprehensive insurance coverage for complete peace of mind.</p>
                    </div>
                    
                <div class="feature-card stagger-item">
                    <div class="feature-icon">🔧</div>
                    <h3 class="feature-title">Regular Maintenance</h3>
                    <p class="feature-description">Our fleet undergoes rigorous maintenance checks to ensure optimal performance and safety.</p>
                    </div>
                    
                <div class="feature-card stagger-item">
                    <div class="feature-icon">📍</div>
                        <h3 class="feature-title">Multiple Locations</h3>
                    <p class="feature-description">Convenient pickup and drop-off points across the city for your flexibility.</p>
                    </div>
                    
                <div class="feature-card stagger-item">
                    <div class="feature-icon">💬</div>
                        <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Round-the-clock customer support to assist you whenever you need help.</p>
                </div>
                
                <div class="feature-card stagger-item">
                    <div class="feature-icon">💰</div>
                    <h3 class="feature-title">Best Prices</h3>
                    <p class="feature-description">Competitive pricing with no hidden fees. The best value for your money guaranteed.</p>
                    </div>
                </div>
            </div>
        </section>
    <!-- Booking Modal -->
    <div id="bookingModal" class="booking-modal">
        <div class="booking-modal-content">
            <div class="booking-modal-header">
                <h2 id="bookingModalTitle">Book Vehicle</h2>
                <button class="close-modal" onclick="closeBookingModal()">&times;</button>
            </div>
            <div class="booking-modal-body" id="bookingModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    </main>

<script>
// Wishlist functionality
document.addEventListener('DOMContentLoaded', function() {
    const wishlistButtons = document.querySelectorAll('.vehicle-wishlist-btn');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const vehicleId = this.getAttribute('data-vehicle-id');
            const isActive = this.classList.contains('active');
            
            // Toggle wishlist state
            this.classList.toggle('active');
            
            // Send AJAX request to update wishlist
            fetch('wishlist.php?action=toggle&id=' + vehicleId + '&redirect=index.php')
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }
                    return response.text();
                })
                .then(data => {
                    // Update icon based on new state
                    if (this.classList.contains('active')) {
                        this.setAttribute('title', 'Remove from wishlist');
                    } else {
                        this.setAttribute('title', 'Add to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.classList.toggle('active');
                });
        });
    });

    // Category tabs now link to category.php pages - no JavaScript needed
    // Keeping this for any future enhancements
});

function openBookingModal(category) {
    const modal = document.getElementById('bookingModal');
    const modalBody = document.getElementById('bookingModalBody');
    const modalTitle = document.getElementById('bookingModalTitle');
    
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    
    if (!isLoggedIn) {
        // Show login prompt
        modalTitle.textContent = 'Login Required';
        modalBody.innerHTML = `
            <div class="login-prompt">
                <div class="login-prompt-icon">🔒</div>
                <h3>Please Login First</h3>
                <p>You need to be logged in to book a vehicle from the ${category} category. Please login or create an account to continue.</p>
                <div class="login-prompt-buttons">
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="register.php" class="btn-register">Create Account</a>
                    </div>
                </div>
        `;
    } else {
        // Show booking form for category
        const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
        modalTitle.textContent = `Book ${categoryName}`;
        
        // Get vehicles for this category
        fetch(`api/vehicles/list.php?category=${category}`)
            .then(response => response.json())
            .then(vehicles => {
                if (vehicles.length === 0) {
                    modalBody.innerHTML = `
                        <div class="login-prompt">
                            <div class="login-prompt-icon">🚗</div>
                            <h3>No Vehicles Available</h3>
                            <p>There are no vehicles available in the ${category} category at the moment.</p>
            </div>
                    `;
                } else {
                    // Sort vehicles: premium first, then by price (lowest first)
                    vehicles.sort((a, b) => {
                        if (a.is_premium && !b.is_premium) return -1;
                        if (!a.is_premium && b.is_premium) return 1;
                        return parseFloat(a.base_fare) - parseFloat(b.base_fare);
                    });
                    
                    // Get default vehicle (first one - usually cheapest or most popular)
                    const defaultVehicle = vehicles[0];
                    
                    let vehiclesHtml = '<form id="categoryBookingForm" method="POST" action="book.php">';
                    vehiclesHtml += `<input type="hidden" name="vehicle_id" id="hiddenVehicleId" value="${defaultVehicle.id}">`;
                    vehiclesHtml += `
                        <div class="booking-form-group">
                            <label for="vehicleSelect">Select Vehicle <span style="color: #667eea; font-size: 0.85rem;">(Recommended: ${defaultVehicle.name})</span></label>
                            <select id="vehicleSelect" name="vehicle_select" required>
                    `;
                    
                    vehicles.forEach((vehicle, index) => {
                        const imageUrl = vehicle.image_url || 'public/placeholder.jpg';
                        const isDefault = index === 0 ? 'selected' : '';
                        const premiumBadge = vehicle.is_premium ? ' ⭐ Premium' : '';
                        vehiclesHtml += `
                            <option value="${vehicle.id}" data-base-fare="${vehicle.base_fare}" data-rate-per-km="${vehicle.rate_per_km}" ${isDefault}>
                                ${vehicle.name}${premiumBadge} - ₹${(parseFloat(vehicle.base_fare || 0) * 83).toFixed(2)}/day + ₹${(parseFloat(vehicle.rate_per_km || 0) * 83).toFixed(2)}/km
                            </option>
                        `;
                    });
                    
                    vehiclesHtml += `
                            </select>
                    </div>
                        <div class="booking-form-group">
                            <label for="kmDistance">Distance (KM) <span style="color: #667eea; font-size: 0.85rem;">(Default: 10 km)</span></label>
                            <input type="number" id="kmDistance" name="km_distance" min="1" required placeholder="Enter distance in kilometers" value="10">
                </div>
                        <div class="booking-form-group">
                            <label for="pickupType">Pickup Type</label>
                            <select id="pickupType" name="pickup_type" required>
                                <option value="pickup">Pickup</option>
                                <option value="delivery">Home Delivery</option>
                            </select>
            </div>
                        <div class="booking-form-group" id="deliveryAddressGroup" style="display: none;">
                            <label for="deliveryAddress">Delivery Address</label>
                            <textarea id="deliveryAddress" name="delivery_address" rows="3" placeholder="Enter delivery address"></textarea>
            </div>
                        <div class="booking-summary">
                            <div class="booking-summary-item">
                                <span>Base Fare:</span>
                                <span id="summaryBaseFare">$0.00</span>
        </div>
                            <div class="booking-summary-item">
                                <span>Distance Charge:</span>
                                <span id="summaryDistanceCharge">$0.00</span>
                            </div>
                            <div class="booking-summary-item">
                                <span>Total Price:</span>
                                <span id="summaryTotal">$0.00</span>
            </div>
        </div>
                        <button type="submit" class="booking-submit-btn">Confirm Booking</button>
                    </form>
                    `;
                    
                    modalBody.innerHTML = vehiclesHtml;
                    
                    // Setup form handlers
                    setupBookingForm();
                    
                    // Trigger initial price calculation with default values
                    setTimeout(() => {
                        const vehicleSelect = document.getElementById('vehicleSelect');
                        const kmDistance = document.getElementById('kmDistance');
                        if (vehicleSelect && kmDistance) {
                            vehicleSelect.dispatchEvent(new Event('change'));
                            kmDistance.dispatchEvent(new Event('input'));
                        }
                    }, 100);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="login-prompt">
                        <div class="login-prompt-icon">⚠️</div>
                        <h3>Error Loading Vehicles</h3>
                        <p>There was an error loading vehicles. Please try again later.</p>
                    </div>
                `;
            });
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function setupBookingForm() {
    const vehicleSelect = document.getElementById('vehicleSelect');
    const kmDistance = document.getElementById('kmDistance');
    const pickupType = document.getElementById('pickupType');
    const deliveryAddressGroup = document.getElementById('deliveryAddressGroup');
    
    if (vehicleSelect && kmDistance) {
        function updatePrice() {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const baseFare = parseFloat(selectedOption.getAttribute('data-base-fare')) || 0;
                const ratePerKm = parseFloat(selectedOption.getAttribute('data-rate-per-km')) || 0;
                const distance = parseFloat(kmDistance.value) || 0;
                
                const distanceCharge = ratePerKm * distance;
                const total = baseFare + distanceCharge;
                
                // Convert to INR (1 USD = 83 INR)
                const formatINR = (amount) => '₹' + (amount * 83).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                
                document.getElementById('summaryBaseFare').textContent = formatINR(baseFare);
                document.getElementById('summaryDistanceCharge').textContent = formatINR(distanceCharge);
                document.getElementById('summaryTotal').textContent = formatINR(total);
            } else {
                document.getElementById('summaryBaseFare').textContent = '₹0.00';
                document.getElementById('summaryDistanceCharge').textContent = '₹0.00';
                document.getElementById('summaryTotal').textContent = '₹0.00';
            }
        }
        
        vehicleSelect.addEventListener('change', updatePrice);
        kmDistance.addEventListener('input', updatePrice);
    }
    
    if (pickupType && deliveryAddressGroup) {
        pickupType.addEventListener('change', function() {
            if (this.value === 'delivery') {
                deliveryAddressGroup.style.display = 'block';
            } else {
                deliveryAddressGroup.style.display = 'none';
            }
        });
    }
    
    // Handle form submission
    const form = document.getElementById('categoryBookingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const vehicleId = selectedOption ? selectedOption.value : '';
            const hiddenVehicleId = document.getElementById('hiddenVehicleId');
            
            if (vehicleId && hiddenVehicleId) {
                // Update hidden vehicle_id field
                hiddenVehicleId.value = vehicleId;
                
                // Submit the form with all data
                form.submit();
            }
        });
    }
    
    // Update hidden vehicle_id when selection changes
    if (vehicleSelect) {
        vehicleSelect.addEventListener('change', function() {
            const hiddenVehicleId = document.getElementById('hiddenVehicleId');
            if (hiddenVehicleId) {
                hiddenVehicleId.value = this.value;
            }
            // Trigger price update
            if (kmDistance) {
                kmDistance.dispatchEvent(new Event('input'));
            }
        });
    }
}

// Close modal when clicking outside
document.getElementById('bookingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBookingModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>