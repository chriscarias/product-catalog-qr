<?php
require_once '../config/database.php';

$conn = getConnection();

// Get filter and sort parameters
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Build query
$sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE 1=1";

if ($filter_category > 0) {
    $sql .= " AND (p.category_id = $filter_category OR c.parent_id = $filter_category)";
}

// Sorting
switch ($sort) {
    case 'name':
        $sql .= " ORDER BY p.name " . ($order === 'asc' ? 'ASC' : 'DESC');
        break;
    case 'price':
        $sql .= " ORDER BY p.price " . ($order === 'asc' ? 'ASC' : 'DESC');
        break;
    default: // newest
        $sql .= " ORDER BY p.created_at " . ($order === 'asc' ? 'ASC' : 'DESC');
}

$products = $conn->query($sql);

// Get all categories for navigation
$categories = $conn->query("SELECT c.*, p.name as parent_name,
                           (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
                           FROM categories c
                           LEFT JOIN categories p ON c.parent_id = p.id
                           ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name");

// Group categories by parent
$category_tree = [];
$cat_result = $conn->query("SELECT c.*, p.name as parent_name,
                            (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
                            FROM categories c
                            LEFT JOIN categories p ON c.parent_id = p.id
                            ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name");

while ($cat = $cat_result->fetch_assoc()) {
    if ($cat['parent_id'] === null) {
        $category_tree[$cat['id']] = [
            'info' => $cat,
            'children' => []
        ];
    }
}

$cat_result->data_seek(0);
while ($cat = $cat_result->fetch_assoc()) {
    if ($cat['parent_id'] !== null) {
        $category_tree[$cat['parent_id']]['children'][] = $cat;
    }
}

$product_count = $products->num_rows;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog</title>
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
        <div class="catalog-layout">
            <aside class="sidebar">
                <h2>Categories</h2>
                <ul class="category-menu">
                    <li>
                        <a href="index.php" class="<?php echo $filter_category == 0 ? 'active' : ''; ?>">
                            All Products
                        </a>
                    </li>
                    <?php foreach ($category_tree as $parent): ?>
                        <li>
                            <a href="index.php?category=<?php echo $parent['info']['id']; ?>" 
                               class="<?php echo $filter_category == $parent['info']['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($parent['info']['name']); ?>
                            </a>
                            <?php if (!empty($parent['children'])): ?>
                                <ul class="subcategory-menu">
                                    <?php foreach ($parent['children'] as $child): ?>
                                        <li>
                                            <a href="index.php?category=<?php echo $child['id']; ?>"
                                               class="<?php echo $filter_category == $child['id'] ? 'active' : ''; ?>">
                                                <?php echo htmlspecialchars($child['name']); ?>
                                                (<?php echo $child['product_count']; ?>)
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
            <main class="main-content">
                <div class="toolbar">
                    <div class="product-count">
                        Showing <?php echo $product_count; ?> product<?php echo $product_count != 1 ? 's' : ''; ?>
                    </div>
                    
                    <div class="sort-controls">
                        <label for="sort-by">Sort by:</label>
                        <select id="sort-by" onchange="updateSort()">
                            <option value="newest-desc" <?php echo $sort == 'newest' && $order == 'desc' ? 'selected' : ''; ?>>
                                Newest First
                            </option>
                            <option value="newest-asc" <?php echo $sort == 'newest' && $order == 'asc' ? 'selected' : ''; ?>>
                                Oldest First
                            </option>
                            <option value="name-asc" <?php echo $sort == 'name' && $order == 'asc' ? 'selected' : ''; ?>>
                                Name (A-Z)
                            </option>
                            <option value="name-desc" <?php echo $sort == 'name' && $order == 'desc' ? 'selected' : ''; ?>>
                                Name (Z-A)
                            </option>
                            <option value="price-asc" <?php echo $sort == 'price' && $order == 'asc' ? 'selected' : ''; ?>>
                                Price (Low to High)
                            </option>
                            <option value="price-desc" <?php echo $sort == 'price' && $order == 'desc' ? 'selected' : ''; ?>>
                                Price (High to Low)
                            </option>
                        </select>
                    </div>
                </div>
                
                <div class="product-grid">
                    <?php if ($product_count > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-category">
                                    <?php 
                                    if ($product['parent_category_name']) {
                                        echo htmlspecialchars($product['parent_category_name']) . ' › ';
                                    }
                                    echo htmlspecialchars($product['category_name']); 
                                    ?>
                                </div>
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-description">
                                    <?php 
                                    $desc = $product['description'];
                                    echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc); 
                                    ?>
                                </p>
                                <div class="product-footer">
                                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>No products found in this category.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Product Catalog. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        function updateSort() {
            const select = document.getElementById('sort-by');
            const value = select.value.split('-');
            const sort = value[0];
            const order = value[1];
            const category = new URLSearchParams(window.location.search).get('category') || '';
            
            let url = 'index.php?sort=' + sort + '&order=' + order;
            if (category) {
                url += '&category=' + category;
            }
            
            window.location.href = url;
        }
    </script>
</body>
</html>
