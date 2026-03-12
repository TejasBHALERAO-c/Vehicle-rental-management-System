<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/currency.php';

requireLogin();

$pageTitle = 'My Bookings - RideHub';
include 'includes/header.php';

$userId = getUserId();
$message = '';
$error = '';

// Handle cancel booking
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $bookingId = (int)$_GET['id'];
    
    // Verify booking belongs to user
    $checkStmt = $conn->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
    if ($checkStmt !== false) {
        $checkStmt->bind_param("ii", $bookingId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $checkStmt->close();
            
            // Only allow cancellation if status is pending or confirmed
            if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed') {
                $updateStmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
                if ($updateStmt !== false) {
                    $updateStmt->bind_param("ii", $bookingId, $userId);
                    if ($updateStmt->execute()) {
                        $message = 'Booking cancelled successfully!';
                    } else {
                        $error = 'Failed to cancel booking';
                    }
                    $updateStmt->close();
                }
            } else {
                $error = 'This booking cannot be cancelled.';
            }
        } else {
            $error = 'Booking not found or you do not have permission to cancel it.';
            $checkStmt->close();
        }
    }
}

// Get user bookings
$query = "SELECT b.*, v.name as vehicle_name, v.image_url, v.category 
          FROM bookings b 
          JOIN vehicles v ON b.vehicle_id = v.id 
          WHERE b.user_id = ? 
          ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    $bookings = [];
    error_log("SQL Prepare Error: " . $conn->error);
} else {
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    } else {
        $bookings = [];
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
.bookings-section {
    padding: 60px 20px;
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.bookings-section h1 {
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

.bookings-list {
    display: grid;
    gap: 25px;
}

.booking-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    display: flex;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: slideUp 0.6s ease-out both;
    position: relative;
}

.booking-card:nth-child(1) { animation-delay: 0.1s; }
.booking-card:nth-child(2) { animation-delay: 0.2s; }
.booking-card:nth-child(3) { animation-delay: 0.3s; }
.booking-card:nth-child(4) { animation-delay: 0.4s; }
.booking-card:nth-child(5) { animation-delay: 0.5s; }

.booking-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.booking-image {
    width: 250px;
    min-width: 250px;
    height: 200px;
    object-fit: cover;
    background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
}

.placeholder-image {
    width: 250px;
    min-width: 250px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.booking-details {
    padding: 30px;
    flex: 1;
}

.booking-details h3 {
    font-size: 1.6rem;
    margin-bottom: 20px;
    color: #333;
    font-weight: 700;
}

.booking-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: #333;
    font-weight: 700;
}

.booking-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.booking-status.pending {
    background: #fef3c7;
    color: #92400e;
}

.booking-status.confirmed {
    background: #d1fae5;
    color: #065f46;
}

.booking-status.completed {
    background: #dbeafe;
    color: #1e40af;
}

.booking-status.cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.cancel-booking-btn:hover {
    background: #fecaca !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(153, 27, 27, 0.2);
}

.payment-booking-btn:hover {
    background: #68fd7cff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(153, 27, 27, 0.2);
}

.message {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    animation: slideDown 0.5s ease-out;
}

.message.success {
    background: #d1fae5;
    color: #065f46;
    border: 2px solid #a7f3d0;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #fecaca;
}

@media (max-width: 768px) {
    .booking-card {
        flex-direction: column;
    }
    
    .booking-image,
    .placeholder-image {
        width: 100%;
        height: 200px;
    }
    
    .booking-info {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="bookings-section">
    <div class="container">
        <h1>My Bookings</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="empty-message">
                <div style="font-size: 5rem; margin-bottom: 20px; animation: bounce 2s infinite;">📋</div>
                <p>You haven't made any bookings yet.</p>
                <p style="font-size: 1rem; margin-top: 10px;">Start exploring vehicles and book your perfect ride!</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 30px; display: inline-block; padding: 14px 32px; border-radius: 30px;">Browse Vehicles</a>
            </div>
        <?php else: ?>
            <div class="bookings-list">
                <?php foreach ($bookings as $index => $booking): ?>
                    <?php 
                    $imageUrl = getVehicleImage($booking['vehicle_name'], $booking['category'] ?? '', $booking['image_url'] ?? '');
                    ?>
                    <div class="booking-card">
                        <div class="booking-image-container">
                            <?php if (!empty($imageUrl) && file_exists($imageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($booking['vehicle_name']); ?>" class="booking-image">
                            <?php else: ?>
                                <div class="placeholder-image">🚗</div>
                            <?php endif; ?>
                        </div>
                        <div class="booking-details">
                            <h3><?php echo htmlspecialchars($booking['vehicle_name']); ?></h3>
                            <div class="booking-info">
                                <div class="info-item">
                                    <span class="info-label">Booking ID</span>
                                    <span class="info-value">#<?php echo $booking['id']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Distance</span>
                                    <span class="info-value"><?php echo $booking['km_distance']; ?> KM</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Pickup Type</span>
                                    <span class="info-value"><?php echo $booking['pickup_type'] === 'delivery' ? '🏠 Home Delivery' : '📍 Pickup'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Price</span>
                                    <span class="info-value" style="color: #667eea; font-size: 1.3rem;"><?php echo formatCurrency($booking['estimated_price']); ?></span>
                                </div>
                                  <div class="info-item">
                                    <span class="info-label">DAYS</span>
                                    <span class="info-value"><?php echo $booking['days'];?></span>
                                </div>
                               
                            </div>
                            <?php if (!empty($booking['delivery_address'])): ?>
                                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #667eea;">
                                    <strong style="color: #667eea;">📍 Delivery Address:</strong>
                                    <p style="margin-top: 8px; color: #666;"><?php echo htmlspecialchars($booking['delivery_address']); ?></p>
                                </div>
                            <?php endif; ?>
                            <div style="margin-top: 15px; font-size: 0.85rem; color: #999;">
                                Booked on: <?php echo date('F j, Y g:i A', strtotime($booking['created_at'])); ?>
                            </div>
                             <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="booking-status <?php echo $booking['status']; ?>"><?php echo strtoupper($booking['status']); ?></span>
                                    <span class="info-label">Payment</span>
                                    <span class="booking-status <?php echo $booking['payment_status'] === 'paid' ? 'confirmed' : 'pending'; ?>"><?php echo strtoupper($booking['payment_status']); ?></span>
                                
                                </div>
                                <div class="info-item">
                                    
                            
                            </div>
                            <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
                                    <form method="GET" action="bookings.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="cancel-booking-btn" style="padding: 12px 24px; background: #fee2e2; color: #991b1b; border: 2px solid #fecaca; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; font-size: 0.9rem;">
                                            ❌ Cancel Booking
                                        </button>
                            </form>
                            <form method="POST" action="payment.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="userid" value="<?php echo $booking['user_id'];?>">
                                            <input type="hidden" name="status" value="<?php echo $booking['payment_status'] === 'paid' ? 'confirmed' : 'pending'; ?>">
                                            <input type="hidden" name="days" value="<?php echo $booking['days']?>">
                                          <button type="submit" name="submit" class="payment-booking-btn" style="padding: 12px 24px; background: #73f19fff; color: #10a6f7ff; border: 2px solid #fecaca; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; font-size: 0.9rem;">
                                           Make Payment
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
