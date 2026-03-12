<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/currency.php';

requireLogin();

// Get vehicle ID from GET or POST (for modal booking)
$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0);

if (!$vehicleId) {
    header('Location: index.php');
    exit;
}

// Get vehicle details
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
if ($stmt === false) {
    die("Database error. Please try again later.");
}
$stmt->bind_param("i", $vehicleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: index.php');
    exit;
}

$vehicle = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Pre-fill form from URL parameters (from category booking modal)
$prefillKm = isset($_GET['km']) ? (int)$_GET['km'] : (isset($_POST['km_distance']) ? (int)$_POST['km_distance'] : 10); // Default to 10 km
$prefillPickup = $_GET['pickup'] ?? ($_POST['pickup_type'] ?? 'pickup');
$prefillDelivery = $_GET['delivery'] ?? ($_POST['delivery_address'] ?? '');

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get vehicle ID from POST (from modal form)
    if (isset($_POST['vehicle_id']) && !$vehicleId) {
        $vehicleId = (int)$_POST['vehicle_id'];
        
        // Re-fetch vehicle details if we got ID from POST
        if ($vehicleId) {
            $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
            if ($stmt !== false) {
                $stmt->bind_param("i", $vehicleId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $vehicle = $result->fetch_assoc();
                }
                $stmt->close();
            }
        }
    }
    
    $days=(int)($_POST['days'] ?? 0);

    $kmDistance = (int)($_POST['km_distance'] ?? 0);
    $pickupType = $_POST['pickup_type'] ?? 'pickup';
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');
    
    if ($kmDistance <= 0) {
        $error = 'Distance must be greater than 0';
    } elseif (!$vehicleId || !isset($vehicle)) {
        $error = 'Invalid vehicle selected';
    } else {
        // Calculate price
        $estimatedPrice = (($vehicle['base_fare'] + (($vehicle['rate_per_km'] * $kmDistance)))*$days)*1.00;
        // estimatedPrice * 1.00
        // Create booking
        $userId = getUserId();
        $status = 'pending';
        $paymentStatus = 'unpaid';
        
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_id, km_distance, estimated_price, days, pickup_type, delivery_address, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
        if ($stmt === false) {
            $error = 'Database error. Please try again later.';
            error_log("SQL Prepare Error: " . $conn->error);
        } else {
            $stmt->bind_param("iidssssss", $userId, $vehicleId, $kmDistance, $estimatedPrice,$days, $pickupType, $deliveryAddress, $status, $paymentStatus);
            
            if ($stmt->execute()) {
                $success = 'Booking created successfully! Redirecting to your bookings...';
                header('Refresh: 2; url=bookings.php');
            } else {
                $error = 'Failed to create booking';
                error_log("SQL Execute Error: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// Get vehicle image
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

$vehicleImage = getVehicleImage($vehicle['name'], $vehicle['category'], $vehicle['image_url'] ?? '');

$pageTitle = 'Book Vehicle - RideHub';
include 'includes/header.php';
?>

<style>
.booking-section {
    padding: 60px 20px;
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.booking-section h1 {
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #333;
    text-align: center;
    animation: slideDown 0.6s ease-out;
}

.booking-form-container {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    animation: fadeIn 0.8s ease-out;
}

.vehicle-details {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    animation: slideUp 0.6s ease-out 0.2s both;
}

.vehicle-details h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: #333;
}

.vehicle-details img {
    width: 100%;
    max-width: 400px;
    height: 250px;
    object-fit: cover;
    border-radius: 15px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.vehicle-details p {
    margin-bottom: 15px;
    font-size: 1rem;
    line-height: 1.6;
    color: #666;
}

.vehicle-details strong {
    color: #333;
    font-weight: 700;
}

.booking-form {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    animation: slideUp 0.6s ease-out 0.4s both;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.price-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 25px;
}

.price-display label {
    display: block;
    font-size: 0.9rem;
    margin-bottom: 10px;
    opacity: 0.9;
}

#estimatedPrice {
    font-size: 2.5rem;
    font-weight: 800;
    display: block;
}

.btn {
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
    width: 100%;
    margin-top: 15px;
}

.btn-secondary:hover {
    background: #667eea;
    color: white;
}

.error-message, .success-message {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    animation: slideDown 0.5s ease-out;
}

.error-message.show {
    background: #fee;
    color: #c33;
    border: 2px solid #fcc;
}

.success-message.show {
    background: #efe;
    color: #3c3;
    border: 2px solid #cfc;
}

@media (max-width: 968px) {
    .booking-form-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .vehicle-details,
    .booking-form {
        padding: 30px 20px;
    }
}
</style>

<section class="booking-section">
    <div class="container">
        <h1>Book Vehicle</h1>
        
        <?php if ($error): ?>
            <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message show"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="booking-form-container">
            <div class="vehicle-details">
                <h2><?php echo htmlspecialchars($vehicle['name']); ?></h2>
                <img src="<?php echo htmlspecialchars($vehicleImage); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" onerror="this.src='public/placeholder.jpg'">
                <p><strong>Category:</strong> <span style="color: #667eea; font-weight: 700;"><?php echo htmlspecialchars($vehicle['category']); ?></span></p>
                <p><strong>Base Fare:</strong> <span style="font-size: 1.2rem; color: #333; font-weight: 700;"><?php echo formatCurrency($vehicle['base_fare']); ?></span> per day</p>
                <p><strong>Rate per KM:</strong> <span style="font-size: 1.2rem; color: #333; font-weight: 700;"><?php echo formatCurrency($vehicle['rate_per_km']); ?></span> per kilometer</p>
                <?php if ($vehicle['is_premium']): ?>
                    <p style="margin-top: 15px;"><span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 6px 15px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">⭐ Premium Vehicle</span></p>
                <?php endif; ?>
                <?php if (!empty($vehicle['description'])): ?>
                    <p style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;"><?php echo htmlspecialchars($vehicle['description']); ?></p>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="booking-form">
                <div class="form-group">
                    <label for="km_distance">Distance (KM)</label>
                    <input type="number" id="km_distance" name="km_distance" min="1" required value="<?php echo $prefillKm > 0 ? $prefillKm : 10; ?>" placeholder="Enter distance in kilometers">
                </div>
                
                <div class="form-group">
                    <label for="pickup_type">Pickup Type</label>
                    <select id="pickup_type" name="pickup_type" required>
                        <option value="pickup" <?php echo $prefillPickup === 'pickup' ? 'selected' : ''; ?>>📍 Pickup</option>
                        <option value="delivery" <?php echo $prefillPickup === 'delivery' ? 'selected' : ''; ?>>🏠 Home Delivery</option>
                    </select>
                </div>
                
                <div class="form-group" id="deliveryAddressGroup" style="display: <?php echo $prefillPickup === 'delivery' ? 'block' : 'none'; ?>;">
                    <label for="delivery_address">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" placeholder="Enter your delivery address"><?php echo htmlspecialchars($prefillDelivery); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="days">Days</label>
                    <input type="number" id="days" name="days" min="1" required value="<?php echo $prefilldays > 0 ? $prefilldays : 10; ?>" placeholder="Enter days">
                </div>
                
                <div class="price-display">
                    <label>Estimated Total Price</label>
                    <span id="estimatedPrice"><?php echo formatCurrency($vehicle['base_fare'] + ($vehicle['rate_per_km'] * $prefillKm)); ?></span>
                </div>
                
                <button type="submit" class="btn btn-primary">Confirm Booking</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</section>

<script>
// Show/hide delivery address based on pickup type
document.getElementById('pickup_type').addEventListener('change', function() {
    const deliveryGroup = document.getElementById('deliveryAddressGroup');
    if (this.value === 'delivery') {
        deliveryGroup.style.display = 'block';
        deliveryGroup.style.animation = 'slideDown 0.3s ease-out';
    } else {
        deliveryGroup.style.display = 'none';
    }
});

// Calculate estimated price
const baseFare = <?php echo $vehicle['base_fare']; ?>;
const ratePerKm = <?php echo $vehicle['rate_per_km']; ?>;

function updatePrice() {
    const distance = parseFloat(document.getElementById('km_distance').value) || 0;
     const days = parseFloat(document.getElementById('days').value) || 1;
    const estimatedPrice = (baseFare + (ratePerKm * distance))* days;
            // $estimatedPrice = $vehicle['base_fare'] + (($vehicle['rate_per_km'] * $kmDistance))*$days;

    const priceElement = document.getElementById('estimatedPrice');
    
    // Convert to INR (1 USD = 83 INR)
    const inrPrice = estimatedPrice * 1.00;
    
    // Animate price change
    priceElement.style.transform = 'scale(1.1)';
    priceElement.textContent = '₹' + inrPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    setTimeout(() => {
        priceElement.style.transform = 'scale(1)';
    }, 200);
}

document.getElementById('km_distance').addEventListener('input', updatePrice);
document.getElementById('days').addEventListener('input', updatePrice);

// Initialize price on load
updatePrice();

</script>

<?php include 'includes/footer.php'; ?>
