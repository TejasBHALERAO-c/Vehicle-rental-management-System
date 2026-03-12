# RideHub - Vehicle Rental Application

A web-based vehicle rental application built with PHP, MySQL, HTML, CSS, and JavaScript.

## Prerequisites

Before running this project, make sure you have:

1. **PHP** (version 7.4 or higher)
2. **MySQL** (version 5.7 or higher)
3. **Web Server** (Apache/Nginx) OR use PHP's built-in server
4. **MySQL Client** (for database setup)

## Quick Start Guide

### Step 1: Setup Database
1. Start MySQL service
2. Import the database schema:
   ```bash
   mysql -u root -p < db/schema.sql
   ```
   Or use phpMyAdmin to import `db/schema.sql`

### Step 2: Configure Database
Edit `config/db.php` and update database credentials if needed (default: localhost, root, no password)

### Step 3: Start Web Server

**Option A: PHP Built-in Server (Easiest)**
```bash
php -S localhost:8000
```

**Option B: XAMPP/WAMP**
- Copy project to `htdocs` or `www` folder
- Start Apache and MySQL services
- Access via `http://localhost/project2`

### Step 4: Access Application
Open your browser and navigate to:
- `http://localhost:8000` (PHP built-in server)
- `http://localhost/project2` (XAMPP/WAMP)

## Detailed Setup Instructions

### Option 1: Using XAMPP/WAMP (Recommended for Windows)

1. **Install XAMPP or WAMP**
   - Download from https://www.apachefriends.org/ (XAMPP) or https://www.wampserver.com/ (WAMP)
   - Install and start Apache and MySQL services

2. **Copy Project Files**
   - Copy the project folder to `C:\xampp\htdocs\` (XAMPP) or `C:\wamp64\www\` (WAMP)
   - Or create a virtual host pointing to your project directory

3. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `db/schema.sql` file to create the database and tables
   - Or run the SQL file manually in MySQL

4. **Configure Database Connection**
   - The database config file should be at `config/db.php`
   - Update the database credentials if needed (default: localhost, root, no password)

5. **Start the Application**
   - Open your browser and navigate to: `http://localhost/project2/` or your configured URL

### Option 2: Using PHP Built-in Server

1. **Install PHP and MySQL**
   - Make sure PHP and MySQL are installed and added to your PATH

2. **Setup Database**
   - Start MySQL service
   - Run the SQL file: `mysql -u root -p < db/schema.sql`
   - Or import via MySQL client/phpMyAdmin

3. **Configure Database Connection**
   - Create/update `config/db.php` with your database credentials

4. **Start PHP Server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the Application**
   - Open your browser: `http://localhost:8000`

### Option 3: Using Docker (if configured)

If you have Docker setup, you can use a PHP/MySQL container setup.

## Database Configuration

The application expects a database configuration file at `config/db.php`. Make sure this file exists with the following structure:

```php
<?php
$host = 'localhost';
$dbname = 'vehicle_rental';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

## Project Structure

```
project2/
├── index.html          # Main homepage
├── login.html          # Login page
├── register.html       # Registration page
├── admin.html          # Admin dashboard
├── bookings.html       # User bookings
├── wishlist.html       # User wishlist
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── public/             # Images and assets
├── db/
│   └── schema.sql      # Database schema
├── config/
│   └── db.php          # Database configuration (create this)
└── api/                # API endpoints (create if needed)
    ├── vehicles/
    │   └── list.php    # Vehicle listing API
    └── auth/
        └── login.php   # Authentication API
```

## Default Login Credentials

After running the schema, you can use these test accounts:

- **Admin**: 
  - Email: `admin@vehiclerental.com`
  - Password: (check schema.sql for hashed password)

- **Test Users**:
  - Email: `john@example.com`
  - Email: `jane@example.com`
  - Password: (check schema.sql for hashed password)

**Note**: The passwords in the schema are hashed. You may need to reset them or check the actual password used during development.

## Troubleshooting

1. **Database Connection Error**
   - Check if MySQL is running
   - Verify database credentials in `config/db.php`
   - Ensure database `vehicle_rental` exists

2. **404 Errors for API Endpoints**
   - Make sure the `api/` directory structure exists
   - Check that PHP files are in the correct locations
   - Verify web server is configured to process PHP files

3. **Images Not Loading**
   - Check that the `public/` directory is accessible
   - Verify image paths in the database match actual file locations

## Development Notes

- The application uses PHP for backend API
- Frontend uses vanilla JavaScript (no frameworks)
- Database uses MySQL with foreign key constraints
- Session management for user authentication

