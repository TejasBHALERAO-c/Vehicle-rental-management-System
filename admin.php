<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

requireAdmin();

$pageTitle = 'Admin Panel - RideHub';
include 'includes/header.php';

$message = '';
$error = '';

// Handle vehicle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_vehicle') {
        $id = $_POST['vehicle_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'Bikes';
        $baseFare = floatval($_POST['base_fare'] ?? 0);
        $ratePerKm = floatval($_POST['rate_per_km'] ?? 0);
        $availability = isset($_POST['availability']) ? 1 : 0;
        $isPremium = isset($_POST['is_premium']) ? 1 : 0;
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        
        if (empty($name)) {
            $error = 'Vehicle name is required';
        } else {
            if ($id) {
                // Update existing vehicle
                $stmt = $conn->prepare("UPDATE vehicles SET name=?, category=?, base_fare=?, rate_per_km=?, availability=?, is_premium=?, description=?, image_url=? WHERE id=?");
                $stmt->bind_param("ssddiissi", $name, $category, $baseFare, $ratePerKm, $availability, $isPremium, $description, $imageUrl, $id);
            } else {
                // Insert new vehicle
                $stmt = $conn->prepare("INSERT INTO vehicles (name, category, base_fare, rate_per_km, availability, is_premium, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssddiiss", $name, $category, $baseFare, $ratePerKm, $availability, $isPremium, $description, $imageUrl);
            }
            
            if ($stmt->execute()) {
                $message = $id ? 'Vehicle updated successfully' : 'Vehicle added successfully';
            } else {
                $error = 'Failed to save vehicle';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete_vehicle' && isset($_POST['vehicle_id'])) {
        $id = (int)$_POST['vehicle_id'];
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = 'Vehicle deleted successfully';
        } else {
            $error = 'Failed to delete vehicle';
        }
        $stmt->close();
    }
}

// Get all vehicles
$vehicles = [];
$result = $conn->query("SELECT * FROM vehicles ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

// Get all bookings
$bookings = [];
$result = $conn->query("SELECT b.*, v.name as vehicle_name, u.name as user_name FROM bookings b JOIN vehicles v ON b.vehicle_id = v.id JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Get all users
$users = [];
$result = $conn->query("SELECT id, name, email, phone, is_admin, created_at FROM users ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$activeTab = $_GET['tab'] ?? 'vehicles';
?>

<div class="admin-container">
    <?php if ($message): ?>
        <div class="success-message show"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="admin-tabs">
        <a href="?tab=vehicles" class="tab-btn <?php echo $activeTab === 'vehicles' ? 'active' : ''; ?>">Manage Vehicles</a>
        <a href="?tab=bookings" class="tab-btn <?php echo $activeTab === 'bookings' ? 'active' : ''; ?>">View Bookings</a>
        <a href="?tab=users" class="tab-btn <?php echo $activeTab === 'users' ? 'active' : ''; ?>">Manage Users</a>
    </div>

    <?php if ($activeTab === 'vehicles'): ?>
        <div class="tab-content active">
            <div class="tab-header">
                <h2>Manage Vehicles</h2>
                <a href="?tab=vehicles&action=add" class="btn btn-primary">Add New Vehicle</a>
            </div>
            
            <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                <div class="modal" style="display: block;">
                    <div class="modal-content">
                        <h2>Add Vehicle</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="save_vehicle">
                            <div class="form-group">
                                <label>Vehicle Name</label>
                                <input type="text" name="name" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" required>
                                    <option value="Bikes">Bikes</option>
                                    <option value="Scooters">Scooters</option>
                                    <option value="Cars">Cars</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Base Fare ($)</label>
                                <input type="number" name="base_fare" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Rate Per KM ($)</label>
                                <input type="number" name="rate_per_km" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Image URL</label>
                                <input type="text" name="image_url">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description"></textarea>
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="availability" checked> Available</label>
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="is_premium"> Premium Vehicle</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Vehicle</button>
                            <a href="?tab=vehicles" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Base Fare</th>
                            <th>Rate/KM</th>
                            <th>Available</th>
                            <th>Premium</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo $vehicle['id']; ?></td>
                                <td><?php echo htmlspecialchars($vehicle['name']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['category']); ?></td>
                                <td>$<?php echo number_format($vehicle['base_fare'], 2); ?></td>
                                <td>$<?php echo number_format($vehicle['rate_per_km'], 2); ?></td>
                                <td><?php echo $vehicle['availability'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $vehicle['is_premium'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_vehicle">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($activeTab === 'bookings'): ?>
        <div class="tab-content active">
            <h2>Bookings</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Vehicle</th>
                            <th>Distance</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                                <td><?php echo $booking['km_distance']; ?> KM</td>
                                <td>$<?php echo number_format($booking['estimated_price'], 2); ?></td>
                                <td><?php echo strtoupper($booking['status']); ?></td>
                                <td><?php echo strtoupper($booking['payment_status']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($booking['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="tab-content active">
            <h2>Users</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Admin</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

