<?php
/**
 * Inventory Management System - Logout Page
 * Simple logout confirmation page with redirect functionality
 * Clears session and redirects to login page
 */

session_start();

// Destroy the session
session_destroy();

// In a real application, you might want to:
// 1. Log the logout activity
// 2. Clear any remember me cookies
// 3. Perform other cleanup tasks
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Inventory Management System</title>
    <link rel="stylesheet" href="../css/logout.css">
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">👋</div>
            <h1>You have been logged out</h1>
            <p class="logout-message">Thank you for using the Inventory Management System</p>
            
            <div class="logout-info">
                <div class="info-item">
                    <span class="info-icon">🔒</span>
                    <span>Your session has been securely terminated</span>
                </div>
                <div class="info-item">
                    <span class="info-icon">⏰</span>
                    <span id="countdown">Redirecting in 5 seconds...</span>
                </div>
            </div>
            
            <div class="logout-actions">
                <a href="../pages/login.php" class="btn btn-primary">🔐 Login Again</a>
                <a href="../pages/index.php" class="btn btn-secondary">🏠 Home</a>
            </div>
            
            <div class="logout-footer">
                <p>© 2024 Inventory Management System for SMEs in South C</p>
                <p class="footer-links">
                    <a href="#">Privacy Policy</a> • 
                    <a href="#">Terms of Service</a> • 
                    <a href="#">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Security Message -->
    <div class="security-notice">
        <div class="notice-icon">🛡️</div>
        <p>For security reasons, please close this browser window when you're done</p>
    </div>
    
    <script src="../js/logout.js"></script>
</body>
</html>
