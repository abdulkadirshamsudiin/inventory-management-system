<?php
session_start();
require_once "../includes/db_connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $_SESSION['user'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Inventory Management System</h1>
                <p class="subtitle">For SMEs in South C</p>
            </div>
            
           <form class="login-form" id="loginForm" method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <p style="color: red; margin-bottom: 10px; text-align: center;"><?php echo $error; ?></p>
                    <?php endif; ?>
                    
                    <button type="submit" class="login-btn">Login</button>
                </form>
            <div class="login-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                <p>© 2024 Inventory Management System</p>
            </div>
                    </div>
    </div>
    
    <div class="message" id="message" style="display: none;"></div>
    
    <!-- <script src="../js/login.js"></script> -->
</body>
</html>
