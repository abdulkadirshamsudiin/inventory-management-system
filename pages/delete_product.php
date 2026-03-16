<?php
require_once "../includes/db_connection.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Delete failed: " . $stmt->error;
    }
} else {
    echo "No product ID provided.";
}
?>