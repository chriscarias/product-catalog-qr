<?php
require_once 'auth_check.php';
require_once '../config/database.php';

$conn = getConnection();

// Get statistics
$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NULL");
$stats['categories'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NOT NULL");
$stats['subcategories'] = $result->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Product Catalog Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $stats['products']; ?></div>
                <div class="stat-label">Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-number"><?php echo $stats['categories']; ?></div>
                <div class="stat-label">Categories</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📂</div>
                <div class="stat-number"><?php echo $stats['subcategories']; ?></div>
                <div class="stat-label">Subcategories</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="products.php" class="action-btn">
                    <span class="action-icon">➕</span>
                    Add Product
                </a>
                <a href="categories.php" class="action-btn">
                    <span class="action-icon">📋</span>
                    Manage Categories
                </a>
                <a href="export_qr.php" class="action-btn">
                    <span class="action-icon">📄</span>
                    Export QR Codes
                </a>
                <a href="<?php echo FRONTEND_URL; ?>" class="action-btn" target="_blank">
                    <span class="action-icon">🌐</span>
                    View Catalog
                </a>
            </div>
        </div>
    </div>
</body>
</html>
