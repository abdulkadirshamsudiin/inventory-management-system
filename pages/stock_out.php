<?php
session_start();
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productId = $_POST['productSelect'];
    $quantityRemoved = (int) $_POST['quantityRemoved'];
    $issuedTo = trim($_POST['issuedTo']);
    $stockOutDate = $_POST['stockOutDate'];
    $notes = trim($_POST['notes']);
    $recordedBy = "Admin";

    // First check current quantity
    $checkStmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $checkStmt->bind_param("s", $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $product = $checkResult->fetch_assoc();
        $currentQuantity = (int) $product['quantity'];

        if ($quantityRemoved > $currentQuantity) {
            echo "Error: Not enough stock available.";
            exit();
        }

        // Insert into stock_out table
        $stmt1 = $conn->prepare("INSERT INTO stock_out (product_id, quantity, issued_to, date, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sissss", $productId, $quantityRemoved, $issuedTo, $stockOutDate, $notes, $recordedBy);

        // Update quantity in products table
        $stmt2 = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt2->bind_param("is", $quantityRemoved, $productId);

        if ($stmt1->execute() && $stmt2->execute()) {
            header("Location: products.php");
            exit();
        } else {
            echo "Stock Out failed.";
        }

    } else {
        echo "Product not found.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out - Inventory Management System</title>
    <link rel="stylesheet" href="../css/stock_out.css">
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
                <li><a href="stock_out.php" class="nav-link active">📤 Stock Out</a></li>
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
                <h1>Stock Out</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">👤</div>
                </div>
            </div>
        </header>
        
        <!-- Stock Out Content -->
        <div class="stock-out-content">
            <!-- Stock Out Form -->
            <section class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2>📤 Record Stock Out</h2>
                        <p>Remove inventory from your stock</p>
                    </div>
                    <form class="stock-out-form" id="stockOutForm" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productSelect">Select Product *</label>
                                <select id="productSelect" name="productSelect" required>
                                    <option value="">Choose a product...</option>
                                    <?php
                                    $sql = "SELECT id, name, quantity FROM products ORDER BY name ASC";
                                    $result = $conn->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['id'] . " - " . $row['name'] . " (" . $row['quantity'] . " available)</option>";
                                    }
                                    ?>
                                </select>
                                <span class="error-message" id="productSelectError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantityRemoved">Quantity Removed *</label>
                                <input type="number" id="quantityRemoved" name="quantityRemoved" min="1" required>
                                <span class="error-message" id="quantityRemovedError"></span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="issuedTo">Issued To / Customer *</label>
                                <input type="text" id="issuedTo" name="issuedTo" required>
                                <span class="error-message" id="issuedToError"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="stockOutDate">Date *</label>
                                <!-- <input type="date" id="stockOutDate" name="stockOutDate" required> -->
                                <input type="date" id="stockOutDate" name="stockOutDate" value="<?php echo date('Y-m-d'); ?>" required>
                                <span class="error-message" id="stockOutDateError"></span>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Reason for stock removal..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">📤 Record Stock Out</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">🔄 Reset</button>
                        </div>
                    </form>
                </div>
            </section>
            
            <!-- Stock Out History -->
            <section class="history-section">
                <div class="history-card">
                    <div class="history-header">
                        <h2>📋 Stock Out History</h2>
                        <div class="history-summary">
                            <?php
                            $today = date('Y-m-d');
                            $weekStart = date('Y-m-d', strtotime('monday this week'));

                            $todayQuery = "SELECT SUM(quantity) AS total_today FROM stock_out WHERE date = '$today'";
                            $todayResult = $conn->query($todayQuery);
                            $todayTotal = $todayResult->fetch_assoc()['total_today'];
                            if ($todayTotal == null) {
                                $todayTotal = 0;
                            }

                            $weekQuery = "SELECT SUM(quantity) AS total_week FROM stock_out WHERE date >= '$weekStart'";
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
                                    <th>Issued To</th>
                                    <th>Notes</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $historyQuery = "
                                    SELECT stock_out.date, products.name AS product_name, stock_out.quantity, stock_out.issued_to,  stock_out.notes, stock_out.recorded_by
                                    FROM stock_out
                                    JOIN products ON stock_out.product_id = products.id
                                    ORDER BY stock_out.id DESC
                                ";
                                $historyResult = $conn->query($historyQuery);

                                if ($historyResult && $historyResult->num_rows > 0) {
                                    while ($row = $historyResult->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><span class="quantity-badge">-<?php echo $row['quantity']; ?></span></td>
                                        <td><?php echo htmlspecialchars($row['issued_to']); ?></td>
                                        <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                        <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="6">No stock out records found.</td>
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
                <h3>Stock Out Recorded Successfully!</h3>
            </div>
            <div class="modal-body">
                <p>The stock has been removed from your inventory.</p>
                <div class="stock-summary" id="stockSummary">
                    <!-- Stock details will be shown here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="recordMoreStock()">Record More Stock</button>
                <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>
    </div>
    
    <!-- Message Container -->
    <div class="message" id="message" style="display: none;"></div>
    
    <!-- <script src="../js/stock_out.js"></script> -->
</body>
</html>
