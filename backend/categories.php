<?php
require_once 'auth_check.php';
require_once '../config/database.php';

$conn = getConnection();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            
            if ($name) {
                $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $name, $parent_id);
                
                if ($stmt->execute()) {
                    $message = "Category added successfully";
                } else {
                    $error = "Error adding category: " . $conn->error;
                }
            } else {
                $error = "Category name is required";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "Category deleted successfully";
            } else {
                $error = "Error deleting category. It may have products or subcategories.";
            }
        }
    }
}

// Get all categories
$sql = "SELECT c.*, p.name as parent_name, 
        (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as subcategory_count,
        (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.id
        ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.name";
$categories = $conn->query($sql);

// Get parent categories for dropdown
$parent_categories = $conn->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Product Catalog Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Categories Management</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="parent_id">Parent Category (leave empty for main category)</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">-- Main Category --</option>
                        <?php while ($parent = $parent_categories->fetch_assoc()): ?>
                            <option value="<?php echo $parent['id']; ?>">
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">Add Category</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Existing Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent Category</th>
                        <th>Subcategories</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td>
                                <?php if ($category['parent_id']): ?>
                                    &nbsp;&nbsp;&nbsp;└─
                                <?php endif; ?>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </td>
                            <td>
                                <?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?>
                            </td>
                            <td><?php echo $category['subcategory_count']; ?></td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure? This will delete all subcategories and products in this category.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
