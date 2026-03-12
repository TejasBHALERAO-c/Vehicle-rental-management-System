<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection error. Please check your database configuration.");
}

// Redirect if already logged in
redirectIfLoggedIn();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Get user from database
        $stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
        
        if ($stmt === false) {
            // Show detailed error in development, generic message in production
            $errorMsg = $conn->error;
            if (strpos($errorMsg, "doesn't exist") !== false) {
                $error = 'Database table not found. Please run the database setup script.';
            } else {
                $error = 'Database error. Please try again later.';
            }
            error_log("SQL Prepare Error: " . $errorMsg);
        } else {
            $stmt->bind_param("s", $email);
            
            if (!$stmt->execute()) {
                $error = 'Database error. Please try again later.';
                error_log("SQL Execute Error: " . $stmt->error);
                $stmt->close();
            } else {
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $error = 'Invalid email or password';
                } else {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['is_admin'] = (bool)$user['is_admin'];
                        
                        // Redirect based on user type
                        if ($user['is_admin']) {
                            header('Location: admin.php');
                        } else {
                            header('Location: index.php');
                        }
                        exit;
                    } else {
                        $error = 'Invalid email or password';
                    }
                }
                $stmt->close();
            }
        }
    }
}

$pageTitle = 'Login - RideHub';
include 'includes/header.php';
?>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    width: 100%;
    overflow-x: hidden;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.navbar {
    flex-shrink: 0;
}

.main-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    width: 100%;
    min-height: auto;
}

.auth-container {
    width: 90%;
    max-width: 420px;
    margin: 0 auto;
    position: relative;
}

.auth-box {
    background: white;
    border-radius: 16px;
    padding: 35px 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2), 0 0 0 1px rgba(255,255,255,0.1);
    width: 100%;
    position: relative;
    z-index: 1;
    animation: scaleIn 0.4s ease-out;
}

.auth-header {
    text-align: center;
    margin-bottom: 25px;
    animation: slideDown 0.4s ease-out;
}

.auth-header h1 {
    font-size: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 8px 0;
    font-weight: 700;
}

.auth-header p {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

.auth-form {
    animation: slideUp 0.4s ease-out 0.1s both;
}

.form-group {
    margin-bottom: 18px;
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.form-group .input-wrapper {
    position: relative;
}

.form-group .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #667eea;
    z-index: 1;
}

.form-group input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    font-family: inherit;
    background: #fafafa;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.form-group input::placeholder {
    color: #999;
    font-size: 0.9rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100;
    margin-top: 8px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.error-message, .success-message {
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 18px;
    font-size: 0.9rem;
    animation: slideDown 0.3s ease-out;
}

.error-message.show {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

.success-message.show {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
    animation: fadeIn 0.4s ease-out 0.2s both;
}

.auth-footer p {
    margin-bottom: 8px;
    color: #666;
    font-size: 0.9rem;
}

.auth-footer a {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.auth-footer a:hover {
    color: #764ba2;
}

.forgot-link {
    font-size: 0.85rem;
}

/* Animations */
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        padding: 15px;
        align-items: flex-start;
        padding-top: 40px;
    }
    
    .auth-box {
        padding: 30px 25px;
        border-radius: 14px;
    }
    
    .auth-header h1 {
        font-size: 1.8rem;
    }
    
    .auth-header p {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 10px;
        padding-top: 30px;
    }
    
    .auth-box {
        padding: 25px 20px;
        border-radius: 12px;
    }
    
    .auth-header h1 {
        font-size: 1.6rem;
    }
    
    .form-group input {
        padding: 10px 10px 10px 35px;
    }
    
    .btn-primary {
        padding: 12px;
        font-size: 0.95rem;
    }
}

@media (max-height: 600px) {
    .main-content {
        padding: 10px 0;
        align-items: flex-start;
    }
    
    .auth-box {
        margin: 10px 0;
    }
    
    .auth-header {
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
}

/* Fix for very small screens */
@media (max-width: 360px) {
    .auth-box {
        padding: 20px 15px;
    }
    
    .auth-header h1 {
        font-size: 1.4rem;
    }
    
    .auth-header p {
        font-size: 0.85rem;
    }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<div class="main-content">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>RideHub</h1>
                <p>Welcome Back! Login to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message show"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Login to Your Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
                <p><a href="forgot-password.php" class="forgot-link">Forgot your password?</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Simple form enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    
    if (form) {
        form.addEventListener('submit', function() {
            const button = this.querySelector('.btn-primary');
            if (button) {
                button.disabled = true;
                button.textContent = 'Logging in...';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>