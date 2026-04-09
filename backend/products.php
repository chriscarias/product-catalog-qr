<?php
require_once 'auth_check.php';
require_once '../config/database.php';
require_once 'qr_generator.php';

$conn = getConnection();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = (int)$_POST['category_id'];
            
            if ($name && $price > 0 && $category_id > 0) {
                $sql = "INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdi", $name, $description, $price, $category_id);
                
                if ($stmt->execute()) {
                    $product_id = $conn->insert_id;
                    
                    // Generate QR code
                    $product_url = FRONTEND_URL . '/product.php?id=' . $product_id;
                    $qr_filename = 'product_' . $product_id . '.png';
                    
                    try {
                        QRCodeGenerator::generate($product_url, $qr_filename);
                        
                        // Update product with QR code filename
                        $update_sql = "UPDATE products SET qr_code = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $qr_filename, $product_id);
                        $update_stmt->execute();
                        
                        $message = "Product added successfully with QR code!";
                    } catch (Exception $e) {
                        $message = "Product added but QR code generation failed: " . $e->getMessage();
                    }
                } else {
                    $error = "Error adding product: " . $conn->error;
                }
            } else {
                $error = "Please fill all required fields correctly";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            
            // Get QR filename first
            $qr_sql = "SELECT qr_code FROM products WHERE id = ?";
            $qr_stmt = $conn->prepare($qr_sql);
            $qr_stmt->bind_param("i", $id);
            $qr_stmt->execute();
            $qr_result = $qr_stmt->get_result();
            $qr_data = $qr_result->fetch_assoc();
            
            // Delete product
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete QR code file
                if ($qr_data['qr_code']) {
                    $qr_path = __DIR__ . '/../assets/qrcodes/' . $qr_data['qr_code'];
                    if (file_exists($qr_path)) {
                        unlink($qr_path);
                    }
                }
                $message = "Product deleted successfully";
            } else {
                $error = "Error deleting product: " . $conn->error;
            }
        }
    }
}

// Get filter parameters
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$sql = "SELECT p.*, c.name as category_name, 
        CONCAT(pc.name, ' > ', c.name) as full_category
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE 1=1";

if ($filter_category > 0) {
    $sql .= " AND p.category_id = " . $filter_category;
}

switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    default: // newest
        $sql .= " ORDER BY p.created_at DESC";
}

$products = $conn->query($sql);

// Get all categories for filter and form
$categories = $conn->query("SELECT c.*, p.name as parent_name 
                           FROM categories c 
                           LEFT JOIN categories p ON c.parent_id = p.id 
                           ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name");

// Get categories for add form
$form_categories = $conn->query("SELECT c.*, p.name as parent_name 
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
    <title>Products - Product Catalog Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Products Management</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Product</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php while ($cat = $form_categories->fetch_assoc()): ?>
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
                
                <button type="submit" class="btn">Add Product</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Product List</h2>
            
            <div class="filter-bar">
                <select id="category-filter" onchange="filterProducts()">
                    <option value="0">All Categories</option>
                    <?php 
                    $categories->data_seek(0);
                    while ($cat = $categories->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php 
                            if ($cat['parent_name']) {
                                echo htmlspecialchars($cat['parent_name']) . ' > ';
                            }
                            echo htmlspecialchars($cat['name']); 
                            ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <select id="sort-filter" onchange="filterProducts()">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                </select>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>QR Code</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['full_category'] ?: $product['category_name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php if ($product['qr_code']): ?>
                                        <img src="../assets/qrcodes/<?php echo htmlspecialchars($product['qr_code']); ?>" 
                                             class="qr-preview" alt="QR Code">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-small">View</a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function filterProducts() {
            const category = document.getElementById('category-filter').value;
            const sort = document.getElementById('sort-filter').value;
            window.location.href = `products.php?category=${category}&sort=${sort}`;
        }
    </script>
</body>
</html>
