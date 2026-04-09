<?php
require_once './config/database.php';

// Create database if not exists
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->close();

// Connect to the database
$conn = getConnection();

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    die("Error creating users table: " . $conn->error);
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Categories table created successfully<br>";
} else {
    die("Error creating categories table: " . $conn->error);
}

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Products table created successfully<br>";
} else {
    die("Error creating products table: " . $conn->error);
}

// Insert default admin user (password: admin123)
$password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, email) VALUES ('admin', ?, 'admin@example.com')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $password);

if ($stmt->execute()) {
    echo "Default admin user created (username: admin, password: admin123)<br>";
} else {
    echo "Admin user may already exist<br>";
}

// Insert sample categories
$categories = [
    ['Electronics', null],
    ['Smartphones', 1],
    ['Laptops', 1],
    ['Clothing', null],
    ['Men', 4],
    ['Women', 4],
    ['Books', null],
    ['Fiction', 7],
    ['Non-Fiction', 7]
];

foreach ($categories as $cat) {
    $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $cat[0], $cat[1]);
    $stmt->execute();
}
echo "Sample categories created<br>";

echo "<br><strong>Installation complete!</strong><br>";
echo "You can now <a href='login.php'>login to the backend</a>";

$conn->close();
?>
