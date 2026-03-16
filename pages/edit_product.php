<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_GET['id'])) {
    echo "No product ID provided.";
    exit();
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = trim($_POST['productName']);
    $category = trim($_POST['category']);
    $unitPrice = (float) $_POST['unitPrice'];
    $quantity = (int) $_POST['quantity'];
    $reorderLevel = (int) $_POST['reorderLevel'];

    $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, quantity = ?, reorder_level = ? WHERE id = ?");
    $stmt->bind_param("ssdiss", $productName, $category, $unitPrice, $quantity, $reorderLevel, $id);

    if ($stmt->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../css/add_product.css">
</head>
<body>
    <main class="main-content">
        <header class="top-header">
            <div class="header-content">
                <h1>Edit Product</h1>
                <div class="header-actions">
                    <a href="products.php" class="btn btn-secondary">← Back to Products</a>
                </div>
            </div>
        </header>

        <div class="add-product-content">
            <section class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Edit Product Information</h2>
                        <p>Update the product details below</p>
                    </div>

                    <form class="add-product-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Product ID</label>
                                <input type="text" value="<?php echo $product['id']; ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="productName">Product Name *</label>
                                <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="Food" <?php if ($product['category'] == 'Food') echo 'selected'; ?>>Food</option>
                                    <option value="Greens" <?php if ($product['category'] == 'Greens') echo 'selected'; ?>>Greens</option>
                                    <option value="Beverages" <?php if ($product['category'] == 'Beverages') echo 'selected'; ?>>Beverages</option>
                                    <option value="Personal Care" <?php if ($product['category'] == 'Personal Care') echo 'selected'; ?>>Personal Care</option>
                                    <option value="Household" <?php if ($product['category'] == 'Household') echo 'selected'; ?>>Household</option>
                                    <option value="Snacks & Confectionery" <?php if ($product['category'] == 'Snacks & Confectionery') echo 'selected'; ?>>Snacks & Confectionery</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="unitPrice">Unit Price *</label>
                                <input type="number" id="unitPrice" name="unitPrice" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="quantity">Quantity *</label>
                                <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $product['quantity']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="reorderLevel">Reorder Level *</label>
                                <input type="number" id="reorderLevel" name="reorderLevel" min="0" value="<?php echo $product['reorder_level']; ?>" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                            <a href="products.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>