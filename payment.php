<?php
// payment.php - Standalone animated payment page (Option B)
// Put this file at the project root (same folder as index.php etc.)
require_once __DIR__ . '/includes/header.php';
require_once 'includes/currency.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

$pageTitle = "RideHub — Payment";




$userId = getUserId();
$message = '';
$error = '';

// Handle cancel booking
if(isset($_POST['submit'])){
  $id=(int)$_POST['id'];
  $status=$_POST['status'];
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
  }
    

   
?>

<link rel="stylesheet" href="css/payment.css"><!-- safe to include after header -->
<main class="payment-page">
  <section class="pay-card">
    <h1 class="title">Secure Payment</h1>
    <p class="sub">Complete payment to confirm your booking</p>
    
    <?php foreach ($bookings as $index => $booking): ?>

    <div class="booking-summary">
      <div><strong>Vehicle:</strong> <span id="vehicle-name"><?php echo htmlspecialchars($booking['vehicle_name']); ?></span></div>
      <div><strong>Days:</strong> <span id="booking-days"><?php echo htmlspecialchars($booking['days']); ?></span></div>
      <div><strong>Amount:</strong> <span id="amount-display"><?php echo formatCurrency($booking['estimated_price']); ?></span></div>
    </div>

    <div class="wheel-wrap">
      <div id="wheel" class="spin-wheel">
        <!-- cartoonish wheel — CSS animated -->
        <div class="wheel-inner">
          <div class="segment s1"></div>
          <div class="segment s2"></div>
          <div class="segment s3"></div>
          <div class="segment s4"></div>
        </div>
        <div class="center-badge">Pay</div>
      </div>
      <form onsubmit="return confirm('are you want to pay?');">
      <div id="spinner-msg" class="spinner-msg">Tap <strong>Pay</strong> to start</div>
   </form>
    </div>

    <div class="controls">
      <button id="pay-btn" class="btn-bouncy" onclick="startPayment()">Pay ₹<span id="amount-btn"><?php echo formatCurrency($booking['estimated_price']); ?></span></button>
      <a href="index.php" class="btn-ghost">Back to catalog</a>
    </div>
   

    <div id="processing" class="processing hidden">
      <div class="rings">
        <span class="ring r1"></span>
        <span class="ring r2"></span>
        <span class="ring r3"></span>
      </div>
      <div class="processing-text">Processing payment... <span class="dots">• • •</span>

    </div>
    </div>
<?php 

endforeach;
$stmt->close();
}
?>
</section>
</main>

<script src="js/payment.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
