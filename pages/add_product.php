<?php
session_start();
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  

    $productName = trim($_POST['productName']);
    $category = trim($_POST['category']);
    $unitPrice = (float) $_POST['unitPrice'];
    $quantity = (int) $_POST['quantity'];
    $reorderLevel = (int) $_POST['reorderLevel'];

    $sql = "SELECT id FROM products ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['id'];
        $number = intval(substr($lastId, 1)) + 1;
        $newId = "P" . str_pad($number, 3, "0", STR_PAD_LEFT);
    } else {
        $newId = "P001";
    }

    $stmt = $conn->prepare("INSERT INTO products (id, name, category, price, quantity, reorder_level) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdii", $newId, $productName, $category, $unitPrice, $quantity, $reorderLevel);

    if ($stmt->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Insert failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Inventory Management System</title>
    <link rel="stylesheet" href="../css/add_product.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Inventory System</h2>
            <button class="sidebar-toggle" id="sidebarToggle">☰</button>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="nav-link">📊 Dashboard</a></li>
                <li><a href="products.php" class="nav-link">📦 Products</a></li>
                <li><a href="add_product.php" class="nav-link active">➕ Add Product</a></li>
                <li><a href="stock_in.php" class="nav-link">📥 Stock In</a></li>
                <li><a href="stock_out.php" class="nav-link">📤 Stock Out</a></li>
                <li><a href="low_stock.php" class="nav-link">⚠️ Low Stock</a></li>
                <li><a href="reports.php" class="nav-link">📈 Reports</a></li>
                <li><a href="logout.php" class="nav-link">🚪 Logout</a></li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-content">
                <h1>Add Product</h1>
                <div class="header-actions">
                    <a href="products.php" class="btn btn-secondary">← Back to Products</a>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                        <div class="user-avatar">👤</div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Add Product Content -->
        <div class="add-product-content">
            <section class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Product Information</h2>
                        <p>Enter the details of the new product to add to your inventory</p>
                    </div>
                    
                    <form class="add-product-form" id="addProductForm" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productName">Product Name *</label>
                                <input type="text" id="productName" name="productName" required>
                                <span class="error-message" id="productNameError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Food">Food</option>
                                    <option value="Greens">Greens</option>
                                    <option value="Beverages">Beverages</option>
                                    <option value="Personal Care">Personal Care</option>
                                    <option value="Household">Household</option>
                                    <option value="Snacks & Confectionery">Snacks & Confectionery</option>
                                </select>
                                <span class="error-message" id="categoryError"></span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="unitPrice">Unit Price ($) *</label>
                                <input type="number" id="unitPrice" name="unitPrice" step="0.01" min="0" required>
                                <span class="error-message" id="unitPriceError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity">Initial Quantity *</label>
                                <input type="number" id="quantity" name="quantity" min="0" required>
                                <span class="error-message" id="quantityError"></span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reorderLevel">Reorder Level *</label>
                                <input type="number" id="reorderLevel" name="reorderLevel" min="0" required>
                                <span class="error-message" id="reorderLevelError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="sku">SKU (Optional)</label>
                                <input type="text" id="sku" name="sku" placeholder="Auto-generated if empty">
                                <span class="error-message" id="skuError"></span>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" placeholder="Enter product description..."></textarea>
                            <span class="error-message" id="descriptionError"></span>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">➕ Add Product</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">🔄 Reset</button>
                            <a href="products.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
            
            <!-- Quick Tips Section -->
            <section class="tips-section">
                <div class="tips-card">
                    <h3>💡 Quick Tips</h3>
                    <ul>
                        <li>Product name should be unique and descriptive</li>
                        <li>Reorder level triggers low stock alerts</li>
                        <li>SKU can be left empty for auto-generation</li>
                        <li>Unit price should be in your local currency</li>
                    </ul>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Success Modal -->
    <div class="modal" id="successModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="success-icon">✅</div>
                <h3>Product Added Successfully!</h3>
            </div>
            <div class="modal-body">
                <p>The product has been added to your inventory.</p>
                <div class="product-summary" id="productSummary">
                    <!-- Product details will be shown here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="addAnotherProduct()">Add Another Product</button>
                <a href="products.php" class="btn btn-secondary">View Products</a>
            </div>
        </div>
    </div>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <!-- <script src="../js/add_product.js"></script> -->
</body>
</html>
