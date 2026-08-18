<?php
// ============================================================
// includes/db.php
// Database connection using MySQLi with prepared statements
// ============================================================

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');        // Default XAMPP MySQL user
define('DB_PASS', '');            // Default XAMPP MySQL password (empty)
define('DB_NAME', 'campus_lost_found');
define('DB_PORT', 3307);          // XAMPP MySQL running on custom port 3307

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check for connection errors
if ($conn->connect_error) {
    // In production, log this error instead of displaying it
    die('<div style="font-family:sans-serif;padding:20px;color:red;">
         <h3>Database Connection Failed</h3>
         <p>Error: ' . htmlspecialchars($conn->connect_error) . '</p>
         <p>Make sure XAMPP MySQL is running and the database exists.</p>
         </div>');
}

// Set character encoding to UTF-8
$conn->set_charset('utf8mb4');
?>
