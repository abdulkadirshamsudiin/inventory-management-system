/**
 * Logout Page JavaScript
 * Handles countdown timer, redirect, and user interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    let countdown = 5;
    const countdownElement = document.getElementById('countdown');
    let countdownInterval;
    
    // Start countdown timer
    function startCountdown() {
        countdownInterval = setInterval(function() {
            countdown--;
            
            if (countdownElement) {
                countdownElement.textContent = `Redirecting in ${countdown} second${countdown !== 1 ? 's' : ''}...`;
                
                // Add pulse effect for last 3 seconds
                if (countdown <= 3) {
                    countdownElement.classList.add('pulse');
                }
            }
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                redirectToLogin();
            }
        }, 1000);
    }
    
    // Redirect to login page
    function redirectToLogin() {
        window.location.href = 'login.php';
    }
    
    // Stop countdown if user interacts
    function stopCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        if (countdownElement) {
            countdownElement.textContent = 'Auto-redirect cancelled';
            countdownElement.classList.remove('pulse');
        }
    }
    
    // Add progress bar
    function addProgressBar() {
        const logoutInfo = document.querySelector('.logout-info');
        if (logoutInfo) {
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-bar';
            progressBar.innerHTML = '<div class="progress-fill"></div>';
            logoutInfo.appendChild(progressBar);
        }
    }
    
    // Handle user interactions
    function setupUserInteractions() {
        // Stop countdown on any user interaction
        const logoutCard = document.querySelector('.logout-card');
        if (logoutCard) {
            logoutCard.addEventListener('mouseenter', stopCountdown);
            logoutCard.addEventListener('click', stopCountdown);
            logoutCard.addEventListener('touchstart', stopCountdown);
        }
        
        // Handle button clicks
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Add loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading"></span> Loading...';
                this.style.pointerEvents = 'none';
                
                // Simulate loading delay
                setTimeout(() => {
                    // Navigate to the link destination
                    window.location.href = this.getAttribute('href');
                }, 500);
                
                e.preventDefault();
            });
        });
        
        // Handle footer links
        const footerLinks = document.querySelectorAll('.footer-links a');
        footerLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // For demo purposes, show a message instead of navigation
                showNotification(`${this.textContent} link would open in a new tab`, 'info');
            });
        });
    }
    
    // Show notification (simple implementation)
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'info' ? '#17a2b8' : '#28a745'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Add keyboard shortcuts
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Press 'L' to login immediately
            if (e.key === 'l' || e.key === 'L') {
                stopCountdown();
                redirectToLogin();
            }
            
            // Press 'H' to go home
            if (e.key === 'h' || e.key === 'H') {
                stopCountdown();
                window.location.href = '../index.php';
            }
            
            // Press 'R' to restart countdown
            if (e.key === 'r' || e.key === 'R') {
                stopCountdown();
                countdown = 5;
                startCountdown();
                showNotification('Countdown restarted', 'info');
            }
            
            // Press 'Escape' to stop countdown
            if (e.key === 'Escape') {
                stopCountdown();
                showNotification('Auto-redirect stopped', 'info');
            }
        });
    }
    
    // Add visual effects
    function addVisualEffects() {
        // Add hover effect to security notice
        const securityNotice = document.querySelector('.security-notice');
        if (securityNotice) {
            securityNotice.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(-50%) scale(1.05)';
            });
            
            securityNotice.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(-50%) scale(1)';
            });
        }
        
        // Add ripple effect to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.5);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    if (ripple.parentNode) {
                        ripple.parentNode.removeChild(ripple);
                    }
                }, 600);
            });
        });
    }
    
    // Add CSS for animations
    function addAnimationStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100px);
                }
            }
            
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Log logout event (for analytics/security)
    function logLogoutEvent() {
        const logoutData = {
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            referrer: document.referrer,
            sessionId: sessionStorage.getItem('sessionId') || 'unknown',
            logoutMethod: 'manual'
        };
        
        // In a real application, you would send this to your server
        console.log('Logout event:', logoutData);
        
        // Store in localStorage for debugging
        localStorage.setItem('lastLogout', JSON.stringify(logoutData));
    }
    
    // Initialize everything
    function initialize() {
        addProgressBar();
        setupUserInteractions();
        setupKeyboardShortcuts();
        addVisualEffects();
        addAnimationStyles();
        logLogoutEvent();
        
        // Start countdown after a short delay
        setTimeout(() => {
            startCountdown();
        }, 1000);
        
        // Show keyboard shortcuts hint
        setTimeout(() => {
            showNotification('Press L to login, H for home, R to restart countdown', 'info');
        }, 2000);
    }
    
    // Clean up session data
    function cleanupSession() {
        // Clear any remaining session data
        sessionStorage.clear();
        
        // Clear specific cookies if they exist
        const cookies = document.cookie.split(';');
        cookies.forEach(cookie => {
            const eqPos = cookie.indexOf('=');
            const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
        });
    }
    
    // Run cleanup and initialization
    cleanupSession();
    initialize();
    
    // Add page visibility handling
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Pause countdown when page is not visible
            stopCountdown();
        } else {
            // Resume countdown when page becomes visible again
            if (countdown > 0) {
                startCountdown();
            }
        }
    });
    
    console.log('Logout page initialized successfully');
});
