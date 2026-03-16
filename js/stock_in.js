/**
 * Stock In Page JavaScript
 * Handles form validation, submission, and stock management
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const stockInForm = document.getElementById('stockInForm');
    const messageDiv = document.getElementById('message');
    
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('stockInDate').value = today;
    
    // Product data mapping
    const productData = {
        'P001': 'Rice (5kg bag)',
        'P002': 'Beans (2kg bag)',
        'P003': 'Flour (1kg bag)',
        'P004': 'Royco Cubes (pack of 10)',
        'P005': 'Spinach (bunch)',
        'P006': 'Sukuma Wiki (bunch)',
        'P007': 'Potatoes (1kg)',
        'P008': 'Soda (500ml bottle)',
        'P009': 'Milk (1 liter)',
        'P010': 'Apple Juice (1 liter)',
        'P011': 'Yogurt (500ml)',
        'P012': 'Toilet Paper (4 rolls)',
        'P013': 'Soap (bar)',
        'P014': 'Toothpaste (100ml)',
        'P015': 'Detergent (500g)',
        'P016': 'Match Box (50 sticks)',
        'P017': 'Plastic Bags (roll)',
        'P018': 'Biscuits (pack)',
        'P019': 'Chocolate Bar',
        'P020': 'Chewing Gum (pack)'
    };
    
    // Sidebar toggle functionality
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Form validation and submission
    stockInForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            // Get form data
            const formData = {
                productId: document.getElementById('productSelect').value,
                productName: productData[document.getElementById('productSelect').value],
                quantity: parseInt(document.getElementById('quantityAdded').value),
                supplier: document.getElementById('supplierName').value,
                date: document.getElementById('stockInDate').value,
                notes: document.getElementById('notes').value,
                recordedBy: 'Admin' // In real app, this would be the logged-in user
            };
            
            // Show success modal
            showSuccessModal(formData);
        }
    });
    
    // Real-time validation
    const inputs = stockInForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    // Form validation function
    function validateForm() {
        let isValid = true;
        const requiredFields = ['productSelect', 'quantityAdded', 'supplierName', 'stockInDate'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Individual field validation
    function validateField(field) {
        const fieldName = field.name;
        const fieldValue = field.value.trim();
        const errorElement = document.getElementById(fieldName + 'Error');
        
        // Reset error state
        field.classList.remove('error');
        errorElement.textContent = '';
        
        // Check if field is empty
        if (!fieldValue && field.hasAttribute('required')) {
            field.classList.add('error');
            errorElement.textContent = 'This field is required';
            return false;
        }
        
        // Specific validations
        switch (fieldName) {
            case 'productSelect':
                if (!fieldValue) {
                    field.classList.add('error');
                    errorElement.textContent = 'Please select a product';
                    return false;
                }
                break;
                
            case 'quantityAdded':
                const quantity = parseInt(fieldValue);
                if (isNaN(quantity) || quantity < 1) {
                    field.classList.add('error');
                    errorElement.textContent = 'Quantity must be at least 1';
                    return false;
                }
                if (quantity > 1000) {
                    field.classList.add('error');
                    errorElement.textContent = 'Quantity seems too high';
                    return false;
                }
                break;
                
            case 'supplierName':
                if (fieldValue.length < 2) {
                    field.classList.add('error');
                    errorElement.textContent = 'Supplier name must be at least 2 characters';
                    return false;
                }
                break;
                
            case 'stockInDate':
                const selectedDate = new Date(fieldValue);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate > today) {
                    field.classList.add('error');
                    errorElement.textContent = 'Date cannot be in the future';
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    // Show success modal
    function showSuccessModal(stockData) {
        const modal = document.getElementById('successModal');
        const stockSummary = document.getElementById('stockSummary');
        
        // Display stock summary
        stockSummary.innerHTML = `
            <h4>Stock In Details:</h4>
            <p><strong>Product:</strong> ${stockData.productName}</p>
            <p><strong>Quantity Added:</strong> +${stockData.quantity}</p>
            <p><strong>Supplier:</strong> ${stockData.supplier}</p>
            <p><strong>Date:</strong> ${formatDate(stockData.date)}</p>
            <p><strong>Recorded By:</strong> ${stockData.recordedBy}</p>
            ${stockData.notes ? `<p><strong>Notes:</strong> ${stockData.notes}</p>` : ''}
        `;
        
        modal.style.display = 'flex';
        
        // In real app, this would make an API call to save the stock record
        console.log('Stock data to save:', stockData);
        
        // Update summary counts (for demo purposes)
        updateSummaryCounts(stockData.quantity);
    }
    
    // Update summary counts
    function updateSummaryCounts(quantity) {
        const summaryItems = document.querySelectorAll('.summary-item strong');
        if (summaryItems.length >= 2) {
            // Update today's count
            const todayCount = parseInt(summaryItems[0].textContent) + quantity;
            summaryItems[0].textContent = todayCount;
            
            // Update week count
            const weekCount = parseInt(summaryItems[1].textContent) + quantity;
            summaryItems[1].textContent = weekCount;
        }
    }
    
    // Format date for display
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }
    
    // Reset form
    window.resetForm = function() {
        stockInForm.reset();
        // Reset date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('stockInDate').value = today;
        
        const inputs = stockInForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
        const errorMessages = stockInForm.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            msg.textContent = '';
        });
        showMessage('Form has been reset', 'info');
    };
    
    // Add more stock
    window.addMoreStock = function() {
        closeModal();
        resetForm();
        document.getElementById('productSelect').focus();
    };
    
    // Close modal
    window.closeModal = function() {
        const modal = document.getElementById('successModal');
        modal.style.display = 'none';
    };
    
    // Message function
    function showMessage(text, type = 'info') {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }
    
    // Add new row to history table (for demo purposes)
    function addHistoryRow(stockData) {
        const table = document.querySelector('.history-table tbody');
        const newRow = table.insertRow(0);
        
        const dateTime = new Date().toLocaleString();
        const notes = stockData.notes || '-';
        
        newRow.innerHTML = `
            <td>${dateTime}</td>
            <td>${stockData.productName}</td>
            <td><span class="quantity-badge">+${stockData.quantity}</span></td>
            <td>${stockData.supplier}</td>
            <td>${notes}</td>
            <td>${stockData.recordedBy}</td>
        `;
        
        // Add animation to new row
        newRow.style.animation = 'fadeIn 0.5s ease';
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('successModal');
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
        if (e.ctrlKey && e.key === 'Enter') {
            stockInForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // Add input animations
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Auto-focus on product selection
    document.getElementById('productSelect').focus();
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
    
    console.log('Stock In page initialized successfully');
});
