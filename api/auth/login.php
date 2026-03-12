<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

// Get user from database
$stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

$user = $result->fetch_assoc();

// Verify password (assuming passwords are hashed with password_hash)
if (password_verify($password, $user['password'])) {
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = (bool)$user['is_admin'];
    
    // Determine redirect URL
    $redirect = $user['is_admin'] ? 'admin.html' : 'index.html';
    
    echo json_encode([
        'success' => true,
        'redirect' => $redirect,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'is_admin' => (bool)$user['is_admin']
        ]
    ]);
} else {
    // If password doesn't verify, try checking if it's the plain text password
    // (for development/testing with the seed data)
    // Note: The seed data has hashed passwords, so this might not work
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
}

$stmt->close();
?>

