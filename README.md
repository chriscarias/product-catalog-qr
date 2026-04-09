# Product Catalog - PHP/MySQL Web Application

A complete product catalog management system with back-office administration and public front-end catalog.

## Features

### Back-Office (Admin Panel)
- **User Authentication**: Secure login system
- **Category Management**: 
  - Create main categories and subcategories
  - Hierarchical category structure
  - View category statistics (products, subcategories)
- **Product Management**:
  - Add products with name, description, price, category
  - Automatic QR code generation for each product
  - Filter products by category
  - Sort by name, price, or date (ascending/descending)
  - View detailed product information
- **QR Code Export**:
  - Select products for batch export
  - Filter by category
  - Export QR codes as PDF or printable HTML
  - QR codes link to front-end product pages

### Front-End (Public Catalog)
- **Product Browsing**:
  - Grid layout with product cards
  - Category navigation with hierarchical menu
  - Product detail pages
- **Filtering & Sorting**:
  - Filter by categories and subcategories
  - Sort by: newest/oldest, name (A-Z/Z-A), price (low-high/high-low)
- **Product Details**:
  - Full product information
  - Breadcrumb navigation
  - Related products section
  - Clean, responsive design

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- GD extension for PHP (for QR code generation)

### Setup Instructions

1. **Copy files to your web server**:
   ```bash
   # For XAMPP/WAMP
   cp -r product-catalog/ /path/to/htdocs/
   
   # For Ubuntu/Linux
   cp -r product-catalog/ /var/www/html/
   ```

2. **Configure database connection**:
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'product_catalog');
   
   define('BASE_URL', 'http://localhost/product-catalog');
   ```

3. **Create database and tables**:
   Navigate to: `http://localhost/product-catalog/install.php`
   
   This will:
   - Create the database
   - Create all necessary tables
   - Insert default admin user
   - Add sample categories

4. **Set directory permissions**:
   ```bash
   chmod 755 assets/qrcodes/
   ```

5. **Login to admin panel**:
   - URL: `http://localhost/product-catalog/backend/login.php`
   - Username: `admin`
   - Password: `admin123`

## Directory Structure

```
product-catalog/
├── backend/              # Admin panel
│   ├── login.php        # Login page
│   ├── index.php        # Dashboard
│   ├── categories.php   # Category management
│   ├── products.php     # Product management
│   ├── product_detail.php # Product detail view
│   ├── export_qr.php    # QR code batch selection
│   ├── export_qr_pdf.php # PDF generation
│   ├── qr_generator.php # QR code utility
│   ├── header.php       # Navigation header
│   ├── auth_check.php   # Authentication guard
│   ├── logout.php       # Logout handler
│   └── styles.css       # Backend styles
├── frontend/            # Public catalog
│   ├── index.php        # Product listing
│   ├── product.php      # Product detail page
│   └── styles.css       # Frontend styles
├── config/
│   └── database.php     # Database configuration
├── assets/
│   └── qrcodes/         # Generated QR code images
└── install.php          # Installation script
```

## Database Schema

### users
- `id` - Primary key
- `username` - Unique username
- `password` - Hashed password
- `email` - Email address
- `created_at` - Timestamp

### categories
- `id` - Primary key
- `name` - Category name
- `parent_id` - Foreign key to parent category (NULL for main categories)
- `created_at` - Timestamp

### products
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price (DECIMAL 10,2)
- `category_id` - Foreign key to categories
- `qr_code` - QR code filename
- `created_at` - Timestamp

## Usage Guide

### Adding Categories
1. Go to "Categories" in the admin menu
2. Enter category name
3. Optionally select a parent category for subcategories
4. Click "Add Category"

### Adding Products
1. Go to "Products" in the admin menu
2. Fill in product details:
   - Name (required)
   - Description (optional)
   - Price (required)
   - Category (required)
3. Click "Add Product"
4. QR code is automatically generated and linked to the product

### Exporting QR Codes
1. Go to "Export QR" in the admin menu
2. Filter by category if needed
3. Select products to export
4. Click "Export Selected as PDF"
5. Download the generated PDF or print the HTML page

### Front-End Usage
- Navigate categories using the sidebar menu
- Use sort dropdown to change product order
- Click product cards to view details
- QR codes generated in admin can be scanned to access product pages

## QR Code Implementation

The application generates QR codes that link to product detail pages. Each QR code:
- Is unique to each product
- Links to: `{FRONTEND_URL}/product.php?id={product_id}`
- Is stored in `assets/qrcodes/`
- Can be exported in batches as PDF

**Note**: The current QR generator creates simple pattern-based codes for demonstration. For production use, consider integrating a proper QR library like:
- `phpqrcode` - Pure PHP QR code generator
- `endroid/qr-code` - Modern Composer package
- External API services for high-quality codes

## Customization

### Changing Colors
Edit the CSS files to customize the color scheme:
- Primary color: `#667eea`
- Secondary color: `#764ba2`

### Adding User Registration
The system currently has a single admin user. To add more users:
1. Extend the `users` table with additional fields
2. Create a registration form
3. Add role-based access control

### Enhanced QR Codes
Replace the basic QR generator with a production library:
```bash
composer require endroid/qr-code
```

### Adding Product Images
1. Add `image` column to products table
2. Modify product forms to accept file uploads
3. Update product cards to display images

## Security Considerations

- Change default admin password immediately
- Use strong passwords
- Keep PHP and MySQL updated
- Use prepared statements (already implemented)
- Add CSRF protection for forms
- Implement rate limiting for login attempts
- Use HTTPS in production
- Validate and sanitize all user inputs

## Troubleshooting

### QR Codes not generating
- Check that `assets/qrcodes/` directory exists and is writable
- Verify PHP GD extension is installed: `php -m | grep gd`

### Database connection errors
- Verify credentials in `config/database.php`
- Ensure MySQL service is running
- Check database user has necessary privileges

### Products not showing on front-end
- Ensure products are assigned to valid categories
- Check that category filter is not hiding products
- Verify database connection is working

## Future Enhancements

- Product images and galleries
- Multi-user support with roles
- Product stock management
- Search functionality
- Customer reviews
- Shopping cart integration
- Email notifications
- API for mobile apps
- Analytics dashboard
- CSV import/export

## License

This is a demonstration project for educational purposes.

## Support

For questions or issues, please check the documentation or contact your system administrator.
