<?php
session_start();
require_once "../includes/db_connection.php";

$totalProductsQuery = "SELECT COUNT(*) AS total FROM products";
$totalProductsResult = $conn->query($totalProductsQuery);
$totalProducts = $totalProductsResult->fetch_assoc()['total'];

$totalStockQuery = "SELECT SUM(quantity) AS total_stock FROM products";
$totalStockResult = $conn->query($totalStockQuery);
$totalStock = $totalStockResult->fetch_assoc()['total_stock'];

if ($totalStock == null) {
    $totalStock = 0;
}

$lowStockQuery = "SELECT COUNT(*) AS total FROM products WHERE quantity > 0 AND quantity <= reorder_level";
$lowStockResult = $conn->query($lowStockQuery);
$lowStock = $lowStockResult->fetch_assoc()['total'];

$recentTransactionsQuery = "SELECT 
    (SELECT COUNT(*) FROM stock_in) + 
    (SELECT COUNT(*) FROM stock_out) AS total_transactions";
$recentTransactionsResult = $conn->query($recentTransactionsQuery);
$recentTransactions = $recentTransactionsResult->fetch_assoc()['total_transactions'];
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
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
                <li><a href="dashboard.php" class="nav-link active">📊 Dashboard</a></li>
                <li><a href="products.php" class="nav-link">📦 Products</a></li>
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
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">👤</div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Summary Cards -->
            <section class="summary-cards">
                <div class="card">
                    <div class="card-icon">📦</div>
                    <div class="card-content">
                        <h3>Total Products</h3>
                        <p class="card-number"><?php echo $totalProducts; ?></p>
                        <span class="card-change positive">Products in database</span>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon">📊</div>
                    <div class="card-content">
                        <h3>Total Stock</h3>
                        <p class="card-number"><?php echo $totalStock; ?></p>
                        <span class="card-change positive">Total quantity available</span>
                    </div>
                </div>
                
                <div class="card warning">
                    <div class="card-icon">⚠️</div>
                    <div class="card-content">
                        <h3>Low Stock Items</h3>
                        <p class="card-number"><?php echo $lowStock; ?></p>
                        <span class="card-change negative">Needs attention</span>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon">🔄</div>
                    <div class="card-content">
                        <h3>Recent Transactions</h3>
                        <p class="card-number"><?php echo $recentTransactions; ?></p>
                        <span class="card-change positive">Stock in + stock out records</span>
                    </div>
                </div>
            </section>
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="add_product.php" class="action-btn">
                        <span class="btn-icon">➕</span>
                        <span>Add Product</span>
                    </a>
                    <a href="products.php" class="action-btn">
                        <span class="btn-icon">📦</span>
                        <span>View Products</span>
                    </a>
                    <a href="stock_in.php" class="action-btn">
                        <span class="btn-icon">📥</span>
                        <span>Stock In</span>
                    </a>
                    <a href="stock_out.php" class="action-btn">
                        <span class="btn-icon">📤</span>
                        <span>Stock Out</span>
                    </a>
                    <a href="low_stock.php" class="action-btn">
                        <span class="btn-icon">⚠️</span>
                        <span>Low Stock</span>
                    </a>
                    <a href="reports.php" class="action-btn">
                        <span class="btn-icon">📈</span>
                        <span>Reports</span>
                    </a>
                </div>
            </section>
            
            <!-- Recent Activity -->
            <section class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-table-container">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php
                                $activityQuery = "
                                    SELECT 
                                        stock_in.date AS activity_date,
                                        products.name AS product_name,
                                        'Stock In' AS activity_type,
                                        stock_in.quantity AS quantity,
                                        stock_in.recorded_by AS recorded_by
                                    FROM stock_in
                                    JOIN products ON stock_in.product_id = products.id

                                    UNION ALL

                                    SELECT 
                                        stock_out.date AS activity_date,
                                        products.name AS product_name,
                                        'Stock Out' AS activity_type,
                                        stock_out.quantity AS quantity,
                                        stock_out.recorded_by AS recorded_by
                                    FROM stock_out
                                    JOIN products ON stock_out.product_id = products.id

                                    ORDER BY activity_date DESC
                                    LIMIT 10
                                ";

                                $activityResult = $conn->query($activityQuery);

                                if ($activityResult && $activityResult->num_rows > 0) {
                                    while ($row = $activityResult->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['activity_date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['activity_type'] == 'Stock In' ? 'stock-in' : 'stock-out'; ?>">
                                                <?php echo $row['activity_type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="5">No recent activity found.</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <script src="../js/dashboard.js"></script>
</body>
</html>
