<?php
require_once __DIR__ . '/auth.php';
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
$userName = getUserName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'RideHub - Vehicle Rental'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/ridehub.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <nav class="navbar fade-in">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="index.php">RideHub</a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="bookings.php" class="nav-link">My Bookings</a></li>
                    <li><a href="wishlist.php" class="nav-link">Wishlist</a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="admin.php" class="nav-link">Admin</a></li>
                    <?php endif; ?>
                    <li><span class="nav-link">Welcome, <?php echo htmlspecialchars($userName); ?></span></li>
                    <li><a href="logout.php" class="nav-link logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

