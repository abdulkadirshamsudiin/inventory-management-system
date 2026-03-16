<?php
session_start();
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productId = $_POST['productSelect'];
    $quantityAdded = (int) $_POST['quantityAdded'];
    $supplierName = trim($_POST['supplierName']);
    $stockInDate = $_POST['stockInDate'];
    $notes = trim($_POST['notes']);
    $recordedBy = "Admin";

    // Insert into stock_in table
    $stmt1 = $conn->prepare("INSERT INTO stock_in (product_id, quantity, supplier, date, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sissss", $productId, $quantityAdded, $supplierName, $stockInDate, $notes, $recordedBy);

    // Update quantity in products table
    $stmt2 = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
    $stmt2->bind_param("is", $quantityAdded, $productId);

    if ($stmt1->execute() && $stmt2->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Stock In failed.";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In - Inventory Management System</title>
    <link rel="stylesheet" href="../css/stock_in.css">
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
                <li><a href="stock_in.php" class="nav-link active">📥 Stock In</a></li>
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
                <h1>Stock In</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">👤</div>
                </div>
            </div>
        </header>
        
        <!-- Stock In Content -->
        <div class="stock-in-content">
            <!-- Stock In Form -->
            <section class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2>📥 Record Stock In</h2>
                        <p>Add new inventory to your stock</p>
                    </div>
                    
                    <form class="stock-in-form" id="stockInForm" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productSelect">Select Product *</label>
                                <select id="productSelect" name="productSelect" required>
                                    <option value="">Choose a product...</option>
                                    <?php
                                    $sql = "SELECT id, name FROM products ORDER BY name ASC";
                                    $result = $conn->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['id'] . " - " . $row['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <span class="error-message" id="productSelectError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantityAdded">Quantity Added *</label>
                                <input type="number" id="quantityAdded" name="quantityAdded" min="1" required>
                                <span class="error-message" id="quantityAddedError"></span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="supplierName">Supplier Name *</label>
                                <input type="text" id="supplierName" name="supplierName" required>
                                <span class="error-message" id="supplierNameError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="stockInDate">Date *</label>
                                <!-- <input type="date" id="stockInDate" name="stockInDate" required> -->
                                <input type="date" id="stockInDate" name="stockInDate" value="<?php echo date('Y-m-d'); ?>" required>
                                <span class="error-message" id="stockInDateError"></span>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Additional notes about this stock entry..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">📥 Record Stock In</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">🔄 Reset</button>
                        </div>
                    </form>
                </div>
            </section>
            
            <!-- Stock In History -->
            <section class="history-section">
                <div class="history-card">
                    <div class="history-header">
                        <h2>📋 Stock In History</h2>
                        <div class="history-summary">
                            <?php
                            $today = date('Y-m-d');
                            $weekStart = date('Y-m-d', strtotime('monday this week'));

                            $todayQuery = "SELECT SUM(quantity) AS total_today FROM stock_in WHERE date = '$today'";
                            $todayResult = $conn->query($todayQuery);
                            $todayTotal = $todayResult->fetch_assoc()['total_today'];
                            if ($todayTotal == null) {
                                $todayTotal = 0;
                            }

                            $weekQuery = "SELECT SUM(quantity) AS total_week FROM stock_in WHERE date >= '$weekStart'";
                            $weekResult = $conn->query($weekQuery);
                            $weekTotal = $weekResult->fetch_assoc()['total_week'];
                            if ($weekTotal == null) {
                                $weekTotal = 0;
                            }
                            ?>
                            <span class="summary-item">Total Today: <strong><?php echo $todayTotal; ?></strong></span>
                            <span class="summary-item">This Week: <strong><?php echo $weekTotal; ?></strong></span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Supplier</th>
                                    <th>Notes</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $historyQuery = "
                                    SELECT stock_in.date, products.name AS product_name, stock_in.quantity, stock_in.supplier, stock_in.notes, stock_in.recorded_by
                                    FROM stock_in
                                    JOIN products ON stock_in.product_id = products.id
                                    ORDER BY stock_in.id DESC
                                ";
                                $historyResult = $conn->query($historyQuery);

                                if ($historyResult && $historyResult->num_rows > 0) {
                                    while ($row = $historyResult->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><span class="quantity-badge">+<?php echo $row['quantity']; ?></span></td>
                                        <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                                        <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                        <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="6">No stock in records found.</td>
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
    
    <!-- Success Modal -->
    <div class="modal" id="successModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="success-icon">✅</div>
                <h3>Stock Recorded Successfully!</h3>
            </div>
            <div class="modal-body">
                <p>The stock has been added to your inventory.</p>
                <div class="stock-summary" id="stockSummary">
                    <!-- Stock details will be shown here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="addMoreStock()">Add More Stock</button>
                <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>
    </div>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <!-- <script src="../js/stock_in.js"></script> -->
</body>
</html>
