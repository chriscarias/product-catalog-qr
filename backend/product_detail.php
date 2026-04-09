<?php
require_once 'auth_check.php';
require_once '../config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
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
    header('Location: products.php');
    exit;
}

$product_url = FRONTEND_URL . '/product.php?id=' . $product_id;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Detail</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Product Details</h1>
        
        <div class="card">
            <div class="product-detail">
                <div class="product-info">
                    <div class="info-row">
                        <div class="info-label">Product ID:</div>
                        <div><?php echo $product['id']; ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Name:</div>
                        <div><?php echo htmlspecialchars($product['name']); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Description:</div>
                        <div><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Price:</div>
                        <div><strong>$<?php echo number_format($product['price'], 2); ?></strong></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Category:</div>
                        <div>
                            <?php 
                            if ($product['parent_category_name']) {
                                echo htmlspecialchars($product['parent_category_name']) . ' > ';
                            }
                            echo htmlspecialchars($product['category_name']); 
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Date Added:</div>
                        <div><?php echo date('F j, Y, g:i a', strtotime($product['created_at'])); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Product URL:</div>
                        <div>
                            <a href="<?php echo $product_url; ?>" target="_blank">
                                <?php echo $product_url; ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="qr-section">
                    <h3>QR Code</h3>
                    <?php if ($product['qr_code']): ?>
                        <img src="../assets/qrcodes/<?php echo htmlspecialchars($product['qr_code']); ?>" 
                             class="qr-code-large" alt="Product QR Code">
                        <p style="margin-top: 15px;">
                            <a href="../assets/qrcodes/<?php echo htmlspecialchars($product['qr_code']); ?>" 
                               download class="btn btn-small">Download QR Code</a>
                        </p>
                        <p style="margin-top: 10px; color: #666; font-size: 12px;">
                            Scan this QR code to view the product on the front-end catalog
                        </p>
                    <?php else: ?>
                        <p>QR Code not generated</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn btn-secondary">Back to Products</a>
                <a href="<?php echo $product_url; ?>" target="_blank" class="btn">View in Catalog</a>
            </div>
        </div>
    </div>
</body>
</html>
