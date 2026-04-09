<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'webdev01');
define('DB_PASS', 'ocM4Upt6L5');
define('DB_NAME', 'product_catalog');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Base URL configuration
define('BASE_URL', 'http://localhost/product-catalog');
define('FRONTEND_URL', BASE_URL . '/frontend');
define('BACKEND_URL', BASE_URL . '/backend');
?>
