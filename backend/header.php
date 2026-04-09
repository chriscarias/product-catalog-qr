<header class="header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">Product Catalog Admin</a>
        </div>
        <nav class="nav">
            <a href="index.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="categories.php">Categories</a>
            <a href="export_qr.php">Export QR</a>
            <a href="<?php echo FRONTEND_URL; ?>" target="_blank">View Catalog</a>
            <a href="logout.php" class="logout">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </nav>
    </div>
</header>
