<?php
session_start();
require_once "../includes/db_connection.php";
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock - Inventory Management System</title>
    <link rel="stylesheet" href="../css/low_stock.css">
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
                <li><a href="add_product.php" class="nav-link">➕ Add Product</a></li>
                <li><a href="stock_in.php" class="nav-link">📥 Stock In</a></li>
                <li><a href="stock_out.php" class="nav-link">📤 Stock Out</a></li>
                <li><a href="low_stock.php" class="nav-link active">⚠️ Low Stock</a></li>
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
                <h1>⚠️ Low Stock Alerts</h1>
                <div class="header-actions">
                    <a href="stock_in.php" class="btn btn-primary">📥 Stock In</a>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                        <div class="user-avatar">👤</div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Low Stock Content -->
        <div class="low-stock-content">
            <!-- Alert Summary -->
            <?php
                $criticalQuery = "SELECT COUNT(*) AS total FROM products WHERE quantity = 0";
                $criticalResult = $conn->query($criticalQuery);
                $criticalCount = $criticalResult->fetch_assoc()['total'];

                $lowQuery = "SELECT COUNT(*) AS total FROM products WHERE quantity > 0 AND quantity <= reorder_level";
                $lowResult = $conn->query($lowQuery);
                $lowCount = $lowResult->fetch_assoc()['total'];

                $totalAlerts = $criticalCount + $lowCount;
            ?>

                            <section class="alert-summary">
                                <div class="summary-card critical">
                                    <div class="alert-icon">🚨</div>
                                    <div class="alert-content">
                                        <h3>Critical</h3>
                                        <p class="count"><?php echo $criticalCount; ?> Products</p>
                                        <span>Out of stock - Immediate action required</span>
                                    </div>
                                </div>
                                
                                <div class="summary-card warning">
                                    <div class="alert-icon">⚠️</div>
                                    <div class="alert-content">
                                        <h3>Low Stock</h3>
                                        <p class="count"><?php echo $lowCount; ?> Products</p>
                                        <span>Below reorder level - Restock soon</span>
                                    </div>
                                </div>
                                
                                <div class="summary-card total">
                                    <div class="alert-icon">📊</div>
                                    <div class="alert-content">
                                        <h3>Total Alerts</h3>
                                        <p class="count"><?php echo $totalAlerts; ?></p>
                                        <span>Products requiring attention</span>
                                    </div>
                                </div>
                            </section>
            
            <!-- Search and Filter -->
            <section class="search-filter">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search low stock products..." class="search-input">
                    <button class="search-btn" id="searchBtn">🔍</button>
                </div>
                
                <div class="filter-container">
                    <select id="urgencyFilter" class="filter-select">
                        <option value="">All Urgency</option>
                        <option value="critical">Critical (Out of Stock)</option>
                        <option value="low">Low Stock</option>
                    </select>
                    
                    <select id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        <option value="Food">Food</option>
                        <option value="Greens">Greens</option>
                        <option value="Beverages">Beverages</option>
                        <option value="Personal Care">Personal Care</option>
                        <option value="Household">Household</option>
                        <option value="Snacks & Confectionery">Snacks & Confectionery</option>
                    </select>
                </div>
            </section>
            
            <!-- Low Stock Products -->
            <section class="low-stock-products">
    <div class="products-grid">
            <?php
            $sql = "SELECT * FROM products WHERE quantity <= reorder_level ORDER BY quantity ASC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $isCritical = ($row['quantity'] == 0);
                    $cardClass = $isCritical ? "critical" : "low";
                    $badgeText = $isCritical ? "Out of Stock" : "Low Stock";
                    $percentage = 0;

                    if ((int)$row['reorder_level'] > 0) {
                        $percentage = round(($row['quantity'] / $row['reorder_level']) * 100);
                        if ($percentage > 100) {
                            $percentage = 100;
                        }
                    }
            ?>
                <div class="product-card <?php echo $cardClass; ?>" data-category="<?php echo htmlspecialchars($row['category']); ?>" data-urgency="<?php echo $isCritical ? 'critical' : 'low'; ?>">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <span class="badge <?php echo $cardClass; ?>"><?php echo $badgeText; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="product-info">
                            <p><strong>Product ID:</strong> <?php echo $row['id']; ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                            <p><strong>Current Stock:</strong> <span class="stock-count <?php echo $cardClass; ?>"><?php echo $row['quantity']; ?></span></p>
                            <p><strong>Reorder Level:</strong> <?php echo $row['reorder_level']; ?></p>
                            <p><strong>Unit Price:</strong> $<?php echo number_format($row['price'], 2); ?></p>
                        </div>
                        <div class="stock-indicator">
                            <div class="indicator-bar">
                                <div class="indicator-fill <?php echo $cardClass; ?>" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="indicator-text">
                                <?php
                                if ($isCritical) {
                                    echo "0% available";
                                } else {
                                    echo $percentage . "% of reorder level";
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="stock_in.php" class="btn btn-restock">🔄 Restock Now</a>
                        <button class="btn btn-view" onclick="alert('Product ID: <?php echo $row['id']; ?>')">👁️ Details</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No low stock products found.</p>";
            }
            ?>
    </div>
</section>
        </div>
    </main>
    
    <!-- Product Details Modal -->
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
    
    <!-- Restock Modal -->
    <div class="modal" id="restockModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Restock Product</h3>
                <button class="modal-close" onclick="closeRestockModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="restockForm">
                    <div class="form-group">
                        <label>Product Name:</label>
                        <input type="text" id="restockProductName" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current Stock:</label>
                        <input type="text" id="restockCurrentStock" readonly>
                    </div>
                    <div class="form-group">
                        <label>Quantity to Add:</label>
                        <input type="number" id="restockQuantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Supplier:</label>
                        <input type="text" id="restockSupplier" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">🔄 Restock Now</button>
                        <button type="button" class="btn btn-secondary" onclick="closeRestockModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <!-- <script src="../js/low_stock.js"></script> s-->
</body>
</html>
