<?php
require_once 'includes/auth.php';

redirectIfLoggedIn();

$pageTitle = 'Forgot Password - RideHub';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>RideHub</h1>
            <p>Reset Your Password</p>
        </div>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

