<?php
/**
 * Database Setup Script
 * This will create the database and tables if they don't exist
 */

require_once 'config/db.php';

// First, connect without selecting database
$host = 'localhost';
$username = 'root';
$password = '';

$conn_setup = new mysqli($host, $username, $password);

if ($conn_setup->connect_error) {
    die("Connection failed: " . $conn_setup->connect_error);
}

echo "<h2>Database Setup</h2>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS vehicle_rental";
if ($conn_setup->query($sql) === TRUE) {
    echo "<p style='color: green;'>✅ Database 'vehicle_rental' created or already exists</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating database: " . $conn_setup->error . "</p>";
}

$conn_setup->close();

// Now connect to the database
require_once 'config/db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute schema
$schemaFile = __DIR__ . '/db/schema.sql';
if (!file_exists($schemaFile)) {
    die("<p style='color: red;'>❌ Schema file not found: $schemaFile</p>");
}

$schema = file_get_contents($schemaFile);

// Remove CREATE DATABASE and USE statements (we're already connected)
$schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
$schema = preg_replace('/USE.*?;/i', '', $schema);

// Split by semicolons and execute each statement
$statements = array_filter(array_map('trim', explode(';', $schema)));

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty lines and comments
    }
    
    if ($conn->query($statement) === TRUE) {
        $successCount++;
        // Extract table name if it's a CREATE TABLE statement
        if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
            echo "<p style='color: green;'>✅ Created table: " . $matches[1] . "</p>";
        } elseif (preg_match('/INSERT INTO.*?`?(\w+)`?/i', $statement, $matches)) {
            echo "<p style='color: green;'>✅ Inserted data into: " . $matches[1] . "</p>";
        }
    } else {
        $errorCount++;
        // Only show error if it's not "already exists"
        if (strpos($conn->error, 'already exists') === false) {
            echo "<p style='color: orange;'>⚠️ " . $conn->error . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>Setup complete!</strong></p>";
echo "<p>Successful operations: $successCount</p>";
if ($errorCount > 0) {
    echo "<p>Errors (may include 'already exists'): $errorCount</p>";
}

echo "<hr>";
echo "<p><a href='check-db.php'>Check Database Status</a> | <a href='login.php'>Go to Login</a></p>";
?>

