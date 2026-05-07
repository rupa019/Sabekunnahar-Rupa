<?php
// config.php - Database Connection
// XAMPP এর জন্য default settings

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP default username
define('DB_PASS', '');            // XAMPP default password (blank)
define('DB_NAME', 'meowoof_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Database সংযোগ ব্যর্থ হয়েছে: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
