<?php
// payment_success.php
$pageTitle = "RideHub — Payment Success";
require_once __DIR__ . '/includes/header.php';

// Grab fields (if sent)
$vehicle = isset($_GET['vehicle']) ? htmlspecialchars($_GET['vehicle']) : 'Unknown vehicle';
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0';
$txn = isset($_GET['txn']) ? htmlspecialchars($_GET['txn']) : strtoupper(substr(md5(uniqid()),0,10));
$days = isset($_GET['days']) ? (int)$_GET['days'] : 1;
?>
<main class="payment-success">
  <section class="success-card">
    <div class="badge-success">✓</div>
    <h1>Payment Successful</h1>
    <p class="lead">Thanks — your payment was completed.</p>

    <div class="receipt">
      <div><strong>Transaction ID:</strong> <?php echo $txn; ?></div>
      <div><strong>Vehicle:</strong> <?php echo $vehicle; ?></div>
      <div><strong>Days:</strong> <?php echo $days; ?></div>
      <div><strong>Paid:</strong> ₹<?php echo $amount; ?></div>
    </div>

    <div class="success-actions">
      <a href="index.php" class="btn-bouncy">Return to Catalog</a>
      <a href="bookings.php" class="btn-ghost">My Bookings</a>
    </div>
  </section>
</main>

<link rel="stylesheet" href="css/payment.css"><!-- reuse the same styles -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
