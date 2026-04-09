<?php
require_once '../config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Get product details
$sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Get related products from the same category
$related_sql = "SELECT p.*, c.name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = ? AND p.id != ?
                ORDER BY RAND()
                LIMIT 3";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Catalog</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Product Catalog</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="<?php echo BACKEND_URL; ?>/login.php">Admin Login</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> › 
            <?php if ($product['parent_category_name']): ?>
                <a href="index.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['parent_category_name']); ?>
                </a> › 
            <?php endif; ?>
            <a href="index.php?category=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a> › 
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
        
        <div class="product-detail-page">
            <div class="product-main">
                <div class="product-header">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-meta">
                        <span class="category-badge">
                            <?php 
                            if ($product['parent_category_name']) {
                                echo htmlspecialchars($product['parent_category_name']) . ' › ';
                            }
                            echo htmlspecialchars($product['category_name']); 
                            ?>
                        </span>
                        <span class="date-added">
                            Added: <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="product-price-large">
                    $<?php echo number_format($product['price'], 2); ?>
                </div>
                
                <div class="product-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="product-actions">
                    <a href="index.php?category=<?php echo $product['category_id']; ?>" class="btn-secondary">
                        ← Back to Category
                    </a>
                    <a href="index.php" class="btn-secondary">
                        Back to Catalog
                    </a>
                </div>
            </div>
        </div>
        
        <?php if ($related_products->num_rows > 0): ?>
            <div class="related-products">
                <h2>Related Products</h2>
                <div class="product-grid">
                    <?php while ($related = $related_products->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-category">
                                <?php echo htmlspecialchars($related['category_name']); ?>
                            </div>
                            <h3 class="product-name">
                                <a href="product.php?id=<?php echo $related['id']; ?>">
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </a>
                            </h3>
                            <p class="product-description">
                                <?php 
                                $desc = $related['description'];
                                echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc); 
                                ?>
                            </p>
                            <div class="product-footer">
                                <div class="product-price">$<?php echo number_format($related['price'], 2); ?></div>
                                <a href="product.php?id=<?php echo $related['id']; ?>" class="btn-view">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Product Catalog. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
