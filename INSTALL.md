# Installation Guide - Product Catalog

## Quick Start (5 minutes)

### Option 1: Automatic Installation (Recommended)

1. **Extract files to your web server**:
   - XAMPP/WAMP: `C:\xampp\htdocs\product-catalog\`
   - Linux: `/var/www/html/product-catalog/`

2. **Start Apache and MySQL**:
   - XAMPP: Open XAMPP Control Panel, start Apache and MySQL
   - Linux: `sudo service apache2 start && sudo service mysql start`

3. **Configure database** (edit `config/database.php`):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Your MySQL password
   define('DB_NAME', 'product_catalog');
   
   // Update this to match your server
   define('BASE_URL', 'http://localhost/product-catalog');
   ```

4. **Run installer**:
   Open your browser and go to:
   ```
   http://localhost/product-catalog/install.php
   ```

5. **Login to admin**:
   ```
   URL: http://localhost/product-catalog/backend/login.php
   Username: admin
   Password: admin123
   ```

### Option 2: Manual Installation

1. **Create database manually**:
   ```sql
   mysql -u root -p
   ```
   Then run the SQL from `database.sql`:
   ```sql
   source /path/to/product-catalog/database.sql;
   ```

2. **Configure and access** as in Option 1 steps 3-5.

## Post-Installation

### 1. Change Default Password

IMPORTANT: Change the default admin password immediately!

- Login to the admin panel
- Navigate to your profile (when user management is added)
- Or manually update in database:
  ```sql
  UPDATE users SET password = PASSWORD_HASH('your_new_password', PASSWORD_DEFAULT) WHERE username = 'admin';
  ```

### 2. Set Proper Permissions

Linux/Mac:
```bash
cd /var/www/html/product-catalog
chmod 755 assets/qrcodes/
chmod 644 config/database.php
```

Windows (XAMPP):
- Right-click `assets/qrcodes` folder
- Properties → Security
- Ensure Users have Write permissions

### 3. Configure URLs

If your site is not at `http://localhost/product-catalog`, update in `config/database.php`:

```php
// For domain
define('BASE_URL', 'http://yourdomain.com');

// For subdomain
define('BASE_URL', 'http://catalog.yourdomain.com');

// For subfolder
define('BASE_URL', 'http://yourdomain.com/catalog');
```

## XAMPP/WAMP Specific Setup

### XAMPP on Windows

1. Install XAMPP from https://www.apachefriends.org/
2. Extract files to: `C:\xampp\htdocs\product-catalog\`
3. Start XAMPP Control Panel
4. Click "Start" for Apache and MySQL
5. Visit: `http://localhost/product-catalog/install.php`

### XAMPP on Mac

1. Install XAMPP
2. Extract files to: `/Applications/XAMPP/htdocs/product-catalog/`
3. Start Apache and MySQL from XAMPP Manager
4. Visit: `http://localhost/product-catalog/install.php`

### WAMP on Windows

1. Install WAMP from http://www.wampserver.com/
2. Extract files to: `C:\wamp64\www\product-catalog\`
3. Start WAMP (icon should be green)
4. Visit: `http://localhost/product-catalog/install.php`

## Linux Server Setup

### Ubuntu/Debian

```bash
# Install LAMP stack
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-gd libapache2-mod-php

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Copy files
sudo cp -r product-catalog /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/product-catalog
sudo chmod 755 /var/www/html/product-catalog/assets/qrcodes

# Configure MySQL
sudo mysql
CREATE USER 'catalog_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON product_catalog.* TO 'catalog_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update config/database.php with these credentials
```

### CentOS/RHEL

```bash
# Install LAMP
sudo yum install httpd mariadb-server php php-mysql php-gd

# Start services
sudo systemctl start httpd
sudo systemctl start mariadb
sudo systemctl enable httpd
sudo systemctl enable mariadb

# Continue as above for Ubuntu
```

## Production Deployment

### Security Checklist

- [ ] Change default admin password
- [ ] Use HTTPS (SSL certificate)
- [ ] Disable error display: `php_flag display_errors Off`
- [ ] Enable error logging
- [ ] Update database credentials with strong password
- [ ] Restrict database user permissions
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Remove or protect install.php
- [ ] Regular backups of database
- [ ] Keep PHP and MySQL updated

### SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### Performance Optimization

1. **Enable caching** in .htaccess:
   ```apache
   <IfModule mod_expires.c>
     ExpiresActive On
     ExpiresByType text/css "access plus 1 month"
     ExpiresByType application/javascript "access plus 1 month"
     ExpiresByType image/png "access plus 1 year"
   </IfModule>
   ```

2. **Enable compression**:
   ```apache
   <IfModule mod_deflate.c>
     AddOutputFilterByType DEFLATE text/html text/css application/javascript
   </IfModule>
   ```

3. **Database indexing** (already implemented in schema)

## Troubleshooting

### Can't access http://localhost/product-catalog

**Issue**: Page not found
**Solution**: 
- Check Apache is running
- Verify files are in correct directory
- Check Apache DocumentRoot configuration

### Database connection failed

**Issue**: Can't connect to MySQL
**Solution**:
- Verify MySQL is running
- Check credentials in `config/database.php`
- Test connection: `mysql -u root -p`

### QR codes not generating

**Issue**: QR code images missing
**Solution**:
- Check `assets/qrcodes/` exists and is writable
- Verify PHP GD extension: `php -m | grep gd`
- Check file permissions

### 500 Internal Server Error

**Issue**: Server error
**Solution**:
- Check Apache error log: `/var/log/apache2/error.log` (Linux) or XAMPP logs (Windows)
- Verify .htaccess syntax
- Check PHP error log
- Ensure all required PHP extensions are installed

### Products not showing

**Issue**: Front-end shows no products
**Solution**:
- Verify products exist in database
- Check category assignments
- Clear browser cache
- Check for JavaScript errors in console

## Testing Installation

### Verify Backend

1. Login: `http://localhost/product-catalog/backend/login.php`
2. Add a category: Categories → Add New Category
3. Add a product: Products → Add New Product
4. View QR code: Click "View" on a product
5. Export QR codes: Export QR → Select products → Export

### Verify Frontend

1. Visit: `http://localhost/product-catalog/frontend/`
2. Browse categories in sidebar
3. Click on a product
4. Test sorting and filtering

## Getting Help

### Common Commands

```bash
# Restart Apache
sudo systemctl restart apache2  # Linux
# Or use XAMPP/WAMP control panel

# Restart MySQL
sudo systemctl restart mysql

# Check Apache status
sudo systemctl status apache2

# View Apache error log
tail -f /var/log/apache2/error.log

# View PHP info
php -v
php -m  # List modules
```

### Directory Structure Verification

After installation, you should have:
```
product-catalog/
├── assets/qrcodes/  ← Must be writable
├── backend/
├── config/
├── frontend/
├── .htaccess
├── install.php
├── database.sql
└── README.md
```

## Next Steps

After successful installation:

1. Add your categories (Categories menu)
2. Add products (Products menu)
3. Customize styles (edit CSS files)
4. Configure email settings (for future enhancements)
5. Set up regular backups
6. Plan for additional features

## Support Resources

- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- XAMPP Documentation: https://www.apachefriends.org/faq.html
- Stack Overflow: https://stackoverflow.com/questions/tagged/php

For project-specific questions, refer to README.md or check the inline code comments.
