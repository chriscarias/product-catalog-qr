<?php
require_once 'auth_check.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['products'])) {
    header('Location: export_qr.php');
    exit;
}

$product_ids = array_map('intval', $_POST['products']);

if (empty($product_ids)) {
    header('Location: export_qr.php');
    exit;
}

$conn = getConnection();

// Get selected products
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE p.id IN ($placeholders) AND p.qr_code IS NOT NULL
        ORDER BY p.name";

$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($product_ids));
$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

if (empty($products)) {
    header('Location: export_qr.php');
    exit;
}

// Generate PDF using Python and reportlab
$python_script = __DIR__ . '/generate_qr_pdf.py';
$json_data = json_encode($products);
$json_file = __DIR__ . '/../assets/temp_products.json';
file_put_contents($json_file, $json_data);

$pdf_filename = 'qr_codes_' . date('Y-m-d_His') . '.pdf';
$pdf_path = __DIR__ . '/../assets/' . $pdf_filename;

// Create Python script for PDF generation
$python_code = <<<PYTHON
import json
import sys
from reportlab.lib.pagesizes import letter, A4
from reportlab.pdfgen import canvas
from reportlab.lib.units import inch
from reportlab.lib.utils import ImageReader

# Read product data
with open('$json_file', 'r') as f:
    products = json.load(f)

# Create PDF
c = canvas.Canvas('$pdf_path', pagesize=letter)
width, height = letter

# Title
c.setFont("Helvetica-Bold", 20)
c.drawString(50, height - 50, "Product QR Codes")

# Date
c.setFont("Helvetica", 10)
c.drawString(50, height - 70, "Generated: " + "$$ date('F j, Y, g:i a') $$")

y_position = height - 120
items_per_page = 0
max_items_per_page = 4

for i, product in enumerate(products):
    # Check if we need a new page
    if items_per_page >= max_items_per_page:
        c.showPage()
        y_position = height - 50
        items_per_page = 0
    
    # Product info
    c.setFont("Helvetica-Bold", 14)
    c.drawString(50, y_position, product['name'])
    
    c.setFont("Helvetica", 10)
    y_position -= 20
    
    category = product.get('parent_category_name', '')
    if category:
        category += ' > '
    category += product['category_name']
    c.drawString(50, y_position, f"Category: {category}")
    
    y_position -= 15
    c.drawString(50, y_position, f"Price: \\\${product['price']}")
    
    y_position -= 15
    c.drawString(50, y_position, f"Product ID: {product['id']}")
    
    # QR Code
    if product.get('qr_code'):
        qr_path = "../assets/qrcodes/" + product['qr_code']
        try:
            img = ImageReader(qr_path)
            c.drawImage(img, 400, y_position - 100, 150, 150, preserveAspectRatio=True)
        except:
            c.drawString(400, y_position, "QR Code not found")
    
    # Line separator
    y_position -= 120
    c.line(50, y_position, width - 50, y_position)
    y_position -= 20
    
    items_per_page += 1

c.save()
print("PDF generated successfully")
PYTHON;

file_put_contents($python_script, $python_code);

// Execute Python script
exec("python3 $python_script 2>&1", $output, $return_var);

// Clean up
unlink($json_file);
unlink($python_script);

if ($return_var === 0 && file_exists($pdf_path)) {
    // Force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
    header('Content-Length: ' . filesize($pdf_path));
    readfile($pdf_path);
    
    // Clean up PDF after download
    unlink($pdf_path);
    exit;
} else {
    // PDF generation failed, create a simple HTML version instead
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>QR Codes Export</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { color: #333; }
            .product { margin-bottom: 40px; page-break-inside: avoid; border-bottom: 2px solid #ccc; padding-bottom: 20px; }
            .product-header { display: flex; justify-content: space-between; align-items: flex-start; }
            .product-info { flex: 1; }
            .qr-code { text-align: right; }
            .qr-code img { border: 1px solid #ccc; padding: 10px; background: white; }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <h1>Product QR Codes</h1>
        <p class="no-print">Generated: ' . date('F j, Y, g:i a') . ' | <button onclick="window.print()">Print this page</button></p>
        <hr>
    ';
    
    foreach ($products as $product) {
        echo '<div class="product">
            <div class="product-header">
                <div class="product-info">
                    <h2>' . htmlspecialchars($product['name']) . '</h2>
                    <p><strong>Category:</strong> ';
        
        if ($product['parent_category_name']) {
            echo htmlspecialchars($product['parent_category_name']) . ' &gt; ';
        }
        echo htmlspecialchars($product['category_name']);
        
        echo '</p>
                    <p><strong>Price:</strong> $' . number_format($product['price'], 2) . '</p>
                    <p><strong>Product ID:</strong> ' . $product['id'] . '</p>
                    <p><strong>Description:</strong> ' . nl2br(htmlspecialchars($product['description'])) . '</p>
                </div>
                <div class="qr-code">';
        
        if ($product['qr_code']) {
            echo '<img src="../assets/qrcodes/' . htmlspecialchars($product['qr_code']) . '" width="200" alt="QR Code">';
        }
        
        echo '</div>
            </div>
        </div>';
    }
    
    echo '</body></html>';
    exit;
}
?>
