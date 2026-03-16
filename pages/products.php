<?php
/**
 * Inventory Management System - Products Page
 * Displays all products in a searchable and filterable table
 * Includes action buttons for view, edit, and delete operations
 */

session_start();

require_once "../includes/db_connection.php";

// Check if user is logged in (for demo purposes, we'll skip actual authentication)
// In real application, you would check: if (!isset($_SESSION['user'])) { header('Location: ../pages/login.php'); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Inventory Management System</title>
    <link rel="stylesheet" href="../css/products.css">
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
                <li><a href="products.php" class="nav-link active">📦 Products</a></li>
                <li><a href="add_product.php" class="nav-link">➕ Add Product</a></li>
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
                <h1>Products</h1>
                <div class="header-actions">
                    <a href="add_product.php" class="btn btn-primary">➕ Add Product</a>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                        <div class="user-avatar">👤</div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Products Content -->
        <div class="products-content">
            <!-- Search and Filter Section -->
            <section class="search-filter">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
                    <button class="search-btn" id="searchBtn">🔍</button>
                </div>
                
                <div class="filter-container">
                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        <option value="Food">Food</option>
                        <option value="Greens">Greens</option>
                        <option value="Beverages">Beverages</option>
                        <option value="Personal Care">Personal Care</option>
                        <option value="Household">Household</option>
                        <option value="Snacks & Confectionery">Snacks & Confectionery</option>
                    </select>
                    
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="In Stock">In Stock</option>
                        <option value="Low Stock">Low Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                    </select>
                </div>
            </section>
            
            <!-- Products Table -->
            <section class="products-table-section">
                <div class="table-container">
                    <table class="products-table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM products";
                                $result = $conn->query($sql);

                                while ($row = $result->fetch_assoc()) {

                                    $statusClass = "in-stock";
                                    $statusText = "In Stock";

                                    if ($row['quantity'] == 0) {
                                        $statusClass = "out-of-stock";
                                        $statusText = "Out of Stock";
                                    } elseif ($row['quantity'] <= $row['reorder_level']) {
                                        $statusClass = "low-stock";
                                        $statusText = "Low Stock";
                                    }
                                ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['category']; ?></td>
                                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo $row['reorder_level']; ?></td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <button class="action-btn view-btn" onclick="viewProduct('<?php echo $row['id']; ?>')">👁️</button>
                                            <a href="edit_product.php?id=<?php echo $row['id']; ?>">
                                                <button class="action-btn edit-btn">✏️</button>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                                                <button class="action-btn delete-btn">🗑️</button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                           
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Product Modal -->
    <div class="modal" id="productModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Product Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <script src="../js/products.js"></script>
</body>
</html>
