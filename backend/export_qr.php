<?php
require_once 'auth_check.php';
require_once '../config/database.php';

$conn = getConnection();

// Get all products with QR codes
$sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE p.qr_code IS NOT NULL
        ORDER BY p.created_at DESC";
$products = $conn->query($sql);

// Get all categories for filtering
$categories = $conn->query("SELECT c.*, p.name as parent_name 
                           FROM categories c 
                           LEFT JOIN categories p ON c.parent_id = p.id 
                           ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export QR Codes - Product Catalog Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Export QR Codes</h1>
        
        <div class="card">
            <h2>Select Products to Export</h2>
            <p>Select the products whose QR codes you want to export as a PDF.</p>
            
            <form method="POST" action="export_qr_pdf.php" id="export-form">
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="select-all"> Select All
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="filter-category">Filter by Category:</label>
                    <select id="filter-category">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php 
                                if ($cat['parent_name']) {
                                    echo htmlspecialchars($cat['parent_name']) . ' > ';
                                }
                                echo htmlspecialchars($cat['name']); 
                                ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="checkbox-group">
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <label class="product-checkbox" data-category="<?php echo $product['category_id']; ?>">
                                <input type="checkbox" name="products[]" value="<?php echo $product['id']; ?>">
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                - 
                                <?php 
                                if ($product['parent_category_name']) {
                                    echo htmlspecialchars($product['parent_category_name']) . ' > ';
                                }
                                echo htmlspecialchars($product['category_name']); 
                                ?>
                                - $<?php echo number_format($product['price'], 2); ?>
                            </label>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No products with QR codes found. Add products first.</p>
                    <?php endif; ?>
                </div>
                
                <?php if ($products->num_rows > 0): ?>
                    <div class="form-actions">
                        <button type="submit" class="btn">Export Selected as PDF</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        // Select all checkbox
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="products[]"]:not([style*="display: none"])');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
        
        // Category filter
        document.getElementById('filter-category').addEventListener('change', function() {
            const selectedCategory = this.value;
            const labels = document.querySelectorAll('.product-checkbox');
            
            labels.forEach(label => {
                const checkbox = label.querySelector('input[type="checkbox"]');
                if (selectedCategory === '' || label.dataset.category === selectedCategory) {
                    label.style.display = 'flex';
                } else {
                    label.style.display = 'none';
                    checkbox.checked = false;
                }
            });
            
            // Update select-all checkbox
            document.getElementById('select-all').checked = false;
        });
        
        // Validate form
        document.getElementById('export-form').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input[name="products[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one product to export.');
            }
        });
    </script>
</body>
</html>
