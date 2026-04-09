# Quick Start Guide

## For XAMPP Users (Windows/Mac)

1. **Install XAMPP**: Download from https://www.apachefriends.org/

2. **Copy files**: 
   - Extract `product-catalog` folder to:
     - Windows: `C:\xampp\htdocs\`
     - Mac: `/Applications/XAMPP/htdocs/`

3. **Start services**:
   - Open XAMPP Control Panel
   - Click "Start" for Apache and MySQL (both should turn green)

4. **Install database**:
   - Open browser
   - Go to: `http://localhost/product-catalog/install.php`
   - Wait for success message

5. **Login**:
   - Go to: `http://localhost/product-catalog/backend/login.php`
   - Username: `admin`
   - Password: `admin123`

6. **Start using**:
   - Add categories first
   - Then add products
   - QR codes are generated automatically!

## Default URLs

- **Admin Panel**: http://localhost/product-catalog/backend/
- **Front-end Catalog**: http://localhost/product-catalog/frontend/
- **Installation**: http://localhost/product-catalog/install.php

## Key Features to Try

### In Admin Panel:
1. **Categories** → Add your product categories
2. **Products** → Add products with descriptions and prices
3. **View** any product → See its QR code
4. **Export QR** → Select multiple products and export as PDF

### In Front-end:
1. Browse products by category
2. Sort by price, name, or date
3. Click products to see details

## Important Notes

- ⚠️ **Change the default password** after first login!
- 📁 Make sure `assets/qrcodes/` folder is writable
- 🔒 For production, use HTTPS and strong passwords
- 📱 QR codes link to product pages on your front-end

## Need Help?

- Check `INSTALL.md` for detailed installation steps
- See `README.md` for full documentation
- Ensure Apache and MySQL are running in XAMPP

## What's Next?

1. Customize the design (edit CSS files)
2. Add your product categories
3. Upload your products
4. Share the catalog URL with customers
5. Print QR codes for physical products

Enjoy your new product catalog system! 🚀
