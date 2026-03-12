<?php
/**
 * Database Diagnostic Script
 * Run this file to check if your database is set up correctly
 */

require_once 'config/db.php';

echo "<h2>Database Connection Check</h2>";

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
} else {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
}

// Check if database exists
$result = $conn->query("SELECT DATABASE()");
if ($result) {
    $row = $result->fetch_array();
    echo "<p>Current database: <strong>" . $row[0] . "</strong></p>";
}

// Check if users table exists
echo "<h3>Checking Tables...</h3>";

$tables = ['users', 'vehicles', 'bookings', 'wishlist'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Check table structure
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            echo "<details><summary>Table structure for '$table'</summary><pre>";
            while ($row = $result->fetch_assoc()) {
                echo $row['Field'] . " - " . $row['Type'] . "\n";
            }
            echo "</pre></details>";
        }
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does NOT exist</p>";
    }
}

// Check if users table has data
echo "<h3>Checking Data...</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Users in database: <strong>" . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Cannot query users table</p>";
    echo "<p>Error: " . $conn->error . "</p>";
}

// Test a prepared statement
echo "<h3>Testing Prepared Statement...</h3>";
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
if ($stmt === false) {
    echo "<p style='color: red;'>❌ Prepared statement failed</p>";
    echo "<p>Error: " . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Prepared statement works!</p>";
    $stmt->close();
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If tables don't exist, import the schema: <code>db/schema.sql</code></li>";
echo "<li>If connection fails, check <code>config/db.php</code></li>";
echo "<li>Make sure MySQL is running in XAMPP</li>";
echo "</ol>";
?>

