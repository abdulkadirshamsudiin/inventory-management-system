<?php
session_start();
require_once "../includes/db_connection.php";

$totalStockValueQuery = "SELECT SUM(price * quantity) AS total_value FROM products";
$totalStockValueResult = $conn->query($totalStockValueQuery);
$totalStockValue = $totalStockValueResult->fetch_assoc()['total_value'];
if ($totalStockValue == null) {
    $totalStockValue = 0;
}

$totalProductsQuery = "SELECT COUNT(*) AS total FROM products";
$totalProductsResult = $conn->query($totalProductsQuery);
$totalProducts = $totalProductsResult->fetch_assoc()['total'];

$lowStockQuery = "SELECT COUNT(*) AS total FROM products WHERE quantity <= reorder_level";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockCount = $lowStockResult->fetch_assoc()['total'];

$stockMovementsQuery = "SELECT 
    (SELECT COUNT(*) FROM stock_in) + 
    (SELECT COUNT(*) FROM stock_out) AS total_movements";
$stockMovementsResult = $conn->query($stockMovementsQuery);
$stockMovements = $stockMovementsResult->fetch_assoc()['total_movements'];
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Management System</title>
    <link rel="stylesheet" href="../css/reports.css">
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
                <li><a href="low_stock.php" class="nav-link">⚠️ Low Stock</a></li>
                <li><a href="reports.php" class="nav-link active">📈 Reports</a></li>
                <li><a href="logout.php" class="nav-link">🚪 Logout</a></li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-content">
                <h1>📈 Reports</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="exportReports()">📥 Export Reports</button>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                        <div class="user-avatar">👤</div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Reports Content -->
        <div class="reports-content">
            <!-- Summary Cards -->
            <section class="summary-cards">
                    <div class="summary-card">
                        <div class="card-icon">💰</div>
                        <div class="card-content">
                            <h3>Total Stock Value</h3>
                            <p class="card-number">$<?php echo number_format($totalStockValue, 2); ?></p>
                            <div class="mini-chart">
                                <div class="chart-bar" style="height: 60%"></div>
                                <div class="chart-bar" style="height: 80%"></div>
                                <div class="chart-bar" style="height: 70%"></div>
                                <div class="chart-bar" style="height: 90%"></div>
                                <div class="chart-bar" style="height: 100%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-icon">📦</div>
                        <div class="card-content">
                            <h3>Total Products</h3>
                            <p class="card-number"><?php echo $totalProducts; ?></p>
                            <div class="mini-chart">
                                <div class="chart-bar" style="height: 70%"></div>
                                <div class="chart-bar" style="height: 75%"></div>
                                <div class="chart-bar" style="height: 80%"></div>
                                <div class="chart-bar" style="height: 85%"></div>
                                <div class="chart-bar" style="height: 90%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="summary-card warning">
                        <div class="card-icon">⚠️</div>
                        <div class="card-content">
                            <h3>Low Stock Items</h3>
                            <p class="card-number"><?php echo $lowStockCount; ?></p>
                            <div class="mini-chart">
                                <div class="chart-bar warning" style="height: 40%"></div>
                                <div class="chart-bar warning" style="height: 50%"></div>
                                <div class="chart-bar warning" style="height: 45%"></div>
                                <div class="chart-bar warning" style="height: 55%"></div>
                                <div class="chart-bar warning" style="height: 60%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-icon">🔄</div>
                        <div class="card-content">
                            <h3>Stock Movements</h3>
                            <p class="card-number"><?php echo $stockMovements; ?></p>
                            <div class="mini-chart">
                                <div class="chart-bar" style="height: 80%"></div>
                                <div class="chart-bar" style="height: 65%"></div>
                                <div class="chart-bar" style="height: 90%"></div>
                                <div class="chart-bar" style="height: 75%"></div>
                                <div class="chart-bar" style="height: 85%"></div>
                            </div>
                        </div>
                    </div>
                </section>
            
            <!-- Report Tables -->
            <section class="report-sections">
                <!-- Current Stock Report -->
                <div class="report-section">
                    <div class="section-header">
                        <h2>📊 Current Stock Report</h2>
                        <div class="section-actions">
                            <select class="filter-select" id="stockCategoryFilter">
                                <option value="">All Categories</option>
                                <option value="Food">Food</option>
                                <option value="Greens">Greens</option>
                                <option value="Beverages">Beverages</option>
                                <option value="Personal Care">Personal Care</option>
                                <option value="Household">Household</option>
                                <option value="Snacks & Confectionery">Snacks & Confectionery</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                           <tbody>
                                <?php
                                $currentStockQuery = "SELECT * FROM products ORDER BY name ASC";
                                $currentStockResult = $conn->query($currentStockQuery);

                                if ($currentStockResult && $currentStockResult->num_rows > 0) {
                                    while ($row = $currentStockResult->fetch_assoc()) {

                                        $totalValue = $row['price'] * $row['quantity'];

                                        if ($row['quantity'] == 0) {
                                            $statusClass = "danger";
                                            $statusText = "Out of Stock";
                                        } elseif ($row['quantity'] <= $row['reorder_level']) {
                                            $statusClass = "warning";
                                            $statusText = "Low Stock";
                                        } else {
                                            $statusClass = "good";
                                            $statusText = "In Stock";
                                        }
                                ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                                        <td>$<?php echo number_format($totalValue, 2); ?></td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7">No products found.</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Stock In Report -->
                <div class="report-section">
                    <div class="section-header">
                        <h2>📥 Stock In Report</h2>
                        <div class="section-actions">
                            <select class="filter-select" id="stockInPeriod">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Supplier</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stockInQuery = "
                                    SELECT stock_in.date, products.name AS product_name, stock_in.quantity, stock_in.supplier, products.price, stock_in.recorded_by
                                    FROM stock_in
                                    JOIN products ON stock_in.product_id = products.id
                                    ORDER BY stock_in.id DESC
                                ";
                                $stockInResult = $conn->query($stockInQuery);

                                if ($stockInResult && $stockInResult->num_rows > 0) {
                                    while ($row = $stockInResult->fetch_assoc()) {
                                        $totalCost = $row['price'] * $row['quantity'];
                                ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td class="positive">+<?php echo $row['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                                        <td>$<?php echo number_format($totalCost, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7">No stock in records found.</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Stock Out Report -->
                <div class="report-section">
                    <div class="section-header">
                        <h2>📤 Stock Out Report</h2>
                        <div class="section-actions">
                            <select class="filter-select" id="stockOutPeriod">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Issued To</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stockOutQuery = "
                                    SELECT stock_out.date, products.name AS product_name, stock_out.quantity, stock_out.issued_to, products.price, stock_out.recorded_by
                                    FROM stock_out
                                    JOIN products ON stock_out.product_id = products.id
                                    ORDER BY stock_out.id DESC
                                ";
                                $stockOutResult = $conn->query($stockOutQuery);

                                if ($stockOutResult && $stockOutResult->num_rows > 0) {
                                    while ($row = $stockOutResult->fetch_assoc()) {
                                        $totalValue = $row['price'] * $row['quantity'];
                                ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td class="negative">-<?php echo $row['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($row['issued_to']); ?></td>
                                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                                        <td>$<?php echo number_format($totalValue, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7">No stock out records found.</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <script src="../js/reports.js"></script>
</body>
</html>
