<?php
// Database Configuration
$db_host = "localhost";     // Database host
$db_name = "db_rental";     // Database name
$db_user = "your_username"; // Database username
$db_pass = "your_password"; // Database password

// Create database connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Timezone setting
date_default_timezone_set('Asia/Jakarta');
?>
