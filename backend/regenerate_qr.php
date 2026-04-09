<?php
/**
 * Regenerate QR Codes Script
 * 
 * Run this once after updating the QR generator to regenerate
 * all existing product QR codes with real, scannable codes.
 * 
 * URL: http://localhost/product-catalog/backend/regenerate_qr.php
 */

require_once 'auth_check.php';  // Requires login
require_once '../config/database.php';
require_once 'qr_generator.php';

$conn = getConnection();
$result = $conn->query("SELECT id FROM products WHERE qr_code IS NOT NULL");

$count = 0;
$errors = 0;

echo '<!DOCTYPE html>
<html>
<head>
    <title>Regenerate QR Codes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #666; margin-bottom: 20px; }
        .progress { background: #e9ecef; height: 30px; border-radius: 5px; overflow: hidden; margin: 20px 0; }
        .progress-bar { background: #667eea; height: 100%; transition: width 0.3s; }
        .result { padding: 10px; margin: 5px 0; border-radius: 5px; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Regenerating QR Codes</h1>
        <p class="info">This will delete old QR codes and generate new, scannable ones using the updated generator.</p>
';

while ($product = $result->fetch_assoc()) {
    $product_id = $product['id'];
    $product_url = FRONTEND_URL . '/product.php?id=' . $product_id;
    $qr_filename = 'product_' . $product_id . '.png';
    
    // Delete old QR
    $old_path = __DIR__ . '/../assets/qrcodes/' . $qr_filename;
    if (file_exists($old_path)) {
        unlink($old_path);
    }
    
    // Generate new QR
    try {
        QRCodeGenerator::generate($product_url, $qr_filename);
        echo '<div class="result success">✅ Product ID ' . $product_id . ' - QR regenerated successfully</div>';
        $count++;
    } catch (Exception $e) {
        echo '<div class="result error">❌ Product ID ' . $product_id . ' - Error: ' . $e->getMessage() . '</div>';
        $errors++;
    }
    
    flush();
    ob_flush();
}

$conn->close();

echo '
        <hr>
        <h2>Summary</h2>
        <p><strong>Successfully regenerated:</strong> <span class="success">' . $count . ' QR codes</span></p>
        <p><strong>Errors:</strong> <span class="error">' . $errors . '</span></p>
        
        <p style="margin-top: 30px;">
            <a href="products.php" style="display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">Back to Products</a>
        </p>
    </div>
</body>
</html>
';
?>
