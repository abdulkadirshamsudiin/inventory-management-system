/**
 * Login Page JavaScript
 * Handles password toggle, form validation, and login simulation
 */

document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('loginForm');
    const messageDiv = document.getElementById('message');
    
    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        const eyeIcon = this.querySelector('.eye-icon');
        eyeIcon.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
    });
    
    // Handle form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Basic validation
        if (!username || !password) {
            showMessage('Please fill in all fields', 'error');
            return;
        }
        
        if (username.length < 3) {
            showMessage('Username must be at least 3 characters', 'error');
            return;
        }
        
        if (password.length < 4) {
            showMessage('Password must be at least 4 characters', 'error');
            return;
        }
        
        // Simulate login (for demo purposes)
        // In real application, this would be an AJAX call to server
        showMessage('Login successful! Redirecting...', 'success');
        
        // Simulate redirect after successful login
        setTimeout(function() {
            window.location.href = 'dashboard.php';
        }, 1500);
    });
    
    // Function to show messages
    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';
        
        // Auto-hide message after 3 seconds
        setTimeout(function() {
            messageDiv.style.display = 'none';
        }, 3000);
    }
    
    // Add input animations
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});
