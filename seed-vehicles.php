<?php
/**
 * Robust Vehicle Seed Data Script
 * - Detects columns in `vehicles` table and inserts rows matching column order.
 * - Does NOT change existing table structure or app logic.
 * - Run this after setting up the database.
 */

require_once 'config/db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Helper: get vehicle table columns in order */
$cols = [];
$sql = "SELECT COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vehicles'
        ORDER BY ORDINAL_POSITION";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['COLUMN_NAME'];
    }
} else {
    die("Unable to read table schema for `vehicles`: " . $conn->error);
}

if (count($cols) < 1) {
    die("No columns found for vehicles table.");
}

/* Map of sensible defaults for known columns */
$default_values = [
    'name' => '',
    'category' => 'Cars',
    'is_premium' => 0,
    'base_fare' => 0.00,
    'rate_per_km' => 0.00,
    'availability' => 1,
    'image_url' => '',
    'description' => '',
    'created_at' => null
];

/* Build a list of sample vehicles - each entry is an associative array of known fields.
   We intentionally provide values only for common fields; the script will align them
   to the actual table columns (by name) and fill other columns with sensible defaults. */
$samples = [
    ['name'=>'Honda CB500F','category'=>'Bikes','is_premium'=>0,'base_fare'=>450.00,'rate_per_km'=>9.50,'availability'=>1,'image_url'=>'public/honda_cb500f.jpg','description'=>'Versatile middleweight naked bike, great for city and highway.'],
    ['name'=>'Yamaha MT-07','category'=>'Bikes','is_premium'=>0,'base_fare'=>480.00,'rate_per_km'=>10.00,'availability'=>1,'image_url'=>'public/yamaha_mt07.jpg','description'=>'Nimble street bike with lively torque and engaging ride.'],
    ['name'=>'Kawasaki Ninja 650','category'=>'Bikes','is_premium'=>0,'base_fare'=>520.00,'rate_per_km'=>10.50,'availability'=>1,'image_url'=>'public/kawasaki_ninja650.jpg','description'=>'Sport-touring balance with comfortable ergonomics.'],
    ['name'=>'Royal Enfield Classic 350','category'=>'Bikes','is_premium'=>0,'base_fare'=>380.00,'rate_per_km'=>7.50,'availability'=>1,'image_url'=>'public/re_classic350.jpg','description'=>'Iconic retro cruiser with relaxed riding position.'],
    ['name'=>'KTM Duke 390','category'=>'Bikes','is_premium'=>0,'base_fare'=>420.00,'rate_per_km'=>8.50,'availability'=>1,'image_url'=>'public/ktm_duke390.jpg','description'=>'Lightweight and agile city-focused sport bike.'],
    ['name'=>'TVS iQube','category'=>'Scooters','is_premium'=>0,'base_fare'=>200.00,'rate_per_km'=>3.50,'availability'=>1,'image_url'=>'public/tvs_iqbe.jpg','description'=>'Electric scooter with smooth power delivery and low running cost.'],
    ['name'=>'Ola S1','category'=>'Scooters','is_premium'=>1,'base_fare'=>300.00,'rate_per_km'=>4.00,'availability'=>1,'image_url'=>'public/ola_s1.jpg','description'=>'Premium electric scooter with connected features.'],
    ['name'=>'Maruti Swift','category'=>'Cars','is_premium'=>0,'base_fare'=>1200.00,'rate_per_km'=>12.00,'availability'=>1,'image_url'=>'public/maruti_swift.jpg','description'=>'Reliable hatchback, excellent fuel economy.'],
    ['name'=>'Hyundai Creta','category'=>'Cars','is_premium'=>1,'base_fare'=>2200.00,'rate_per_km'=>18.00,'availability'=>1,'image_url'=>'public/hyundai_creta.jpg','description'=>'Comfortable compact SUV with modern features.'],
    ['name'=>'Mahindra Thar','category'=>'Cars','is_premium'=>1,'base_fare'=>3200.00,'rate_per_km'=>25.00,'availability'=>1,'image_url'=>'public/mahindra_thar.jpg','description'=>'Rugged off-road capable SUV for adventurous trips.'],
    // ... add more entries programmatically to reach a good pool
];

// If samples are fewer than 40, duplicate with slight variations to produce more rows
$full_samples = $samples;
$i = 0;
while (count($full_samples) < 60) {
    $base = $samples[$i % count($samples)];
    $copy = $base;
    $copy['name'] .= ' ' . ($i+1);
    // nudge price slightly
    $copy['base_fare'] = round($copy['base_fare'] * (1 + (($i % 6) * 0.02)),2);
    $copy['rate_per_km'] = round($copy['rate_per_km'] * (1 + (($i % 5) * 0.015)),2);
    $full_samples[] = $copy;
    $i++;
}

/* Prepare INSERT statement: columns found in table, in order */
$insert_cols = $cols; // keep DB order
$placeholders = array_fill(0, count($insert_cols), '?');
$stmt_sql = "INSERT INTO vehicles (" . implode(',', $insert_cols) . ") VALUES (" . implode(',', $placeholders) . ")";
$stmt = $conn->prepare($stmt_sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error . " SQL: " . $stmt_sql);
}

/* Dynamically bind parameters by count using types string. We'll map types based on name heuristics:
   - INT/BOOLEAN columns -> 'i'
   - DECIMAL/NUMERIC -> 'd'
   - Others -> 's' */
function col_type_char($colname){
    $ints = ['id','is_premium','availability'];
    $doubles = ['base_fare','rate_per_km','price','estimated_price'];
    $dates = ['created_at','updated_at'];
    if (in_array($colname, $ints)) return 'i';
    if (in_array($colname, $doubles)) return 'd';
    return 's';
}

/* Insert rows */
$inserted = 0;
foreach ($full_samples as $rowdata) {
    // build values array aligned to $insert_cols
    $vals = [];
    $types = '';
    foreach ($insert_cols as $c) {
        if (array_key_exists($c, $rowdata)) {
            $vals[] = $rowdata[$c];
        } elseif (array_key_exists($c, $default_values)) {
            $vals[] = $default_values[$c];
        } else {
            $vals[] = null;
        }
        $types .= col_type_char($c);
    }
    // bind parameters dynamically
    $refs = [];
    foreach ($vals as $k => $v) $refs[$k] = &$vals[$k];
    array_unshift($refs, $types);
    call_user_func_array([$stmt,'bind_param'], $refs);
    if ($stmt->execute()) {
        $inserted++;
    } else {
        // ignore individual row errors but output once
        echo "<div style='color:orange;'>Warning inserting row: " . htmlspecialchars($stmt->error) . "</div>";
    }
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Seed Vehicles</title></head><body>";
echo "<h1>Vehicle Seed Completed</h1>";
echo "<div>Columns detected: " . implode(', ', $insert_cols) . "</div>";
echo "<div>Rows inserted: $inserted</div>";
echo "<div><a href='index.php'>Back to app</a></div>";
echo "</body></html>";

$stmt->close();
$conn->close();