/**
 * Stock Out Page JavaScript
 * Handles form validation, submission, and stock removal management
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const stockOutForm = document.getElementById('stockOutForm');
    const messageDiv = document.getElementById('message');
    
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('stockOutDate').value = today;
    
    // Product data mapping with available quantities
    const productData = {
        'P001': { name: 'Rice (5kg bag)', available: 45 },
        'P002': { name: 'Beans (2kg bag)', available: 8 },
        'P003': { name: 'Flour (1kg bag)', available: 120 },
        'P004': { name: 'Royco Cubes (pack of 10)', available: 0 },
        'P005': { name: 'Spinach (bunch)', available: 22 },
        'P006': { name: 'Sukuma Wiki (bunch)', available: 35 },
        'P007': { name: 'Potatoes (1kg)', available: 5 },
        'P008': { name: 'Soda (500ml bottle)', available: 85 },
        'P009': { name: 'Milk (1 liter)', available: 12 },
        'P010': { name: 'Apple Juice (1 liter)', available: 28 },
        'P011': { name: 'Yogurt (500ml)', available: 0 },
        'P012': { name: 'Toilet Paper (4 rolls)', available: 40 },
        'P013': { name: 'Soap (bar)', available: 65 },
        'P014': { name: 'Toothpaste (100ml)', available: 8 },
        'P015': { name: 'Detergent (500g)', available: 25 },
        'P016': { name: 'Match Box (50 sticks)', available: 150 },
        'P017': { name: 'Plastic Bags (roll)', available: 0 },
        'P018': { name: 'Biscuits (pack)', available: 55 },
        'P019': { name: 'Chocolate Bar', available: 38 },
        'P020': { name: 'Chewing Gum (pack)', available: 12 }
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
    
    // Product selection change handler
    document.getElementById('productSelect').addEventListener('change', function() {
        const selectedProduct = this.value;
        const quantityInput = document.getElementById('quantityRemoved');
        
        if (selectedProduct && productData[selectedProduct]) {
            const available = productData[selectedProduct].available;
            quantityInput.max = available;
            
            if (available === 0) {
                showMessage('This product is out of stock!', 'warning');
                quantityInput.value = '';
                quantityInput.disabled = true;
            } else {
                quantityInput.disabled = false;
                if (parseInt(quantityInput.value) > available) {
                    quantityInput.value = available;
                }
            }
        } else {
            quantityInput.max = '';
            quantityInput.disabled = false;
        }
    });
    
    // Form validation and submission
    stockOutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            // Get form data
            const formData = {
                productId: document.getElementById('productSelect').value,
                productName: productData[document.getElementById('productSelect').value].name,
                quantity: parseInt(document.getElementById('quantityRemoved').value),
                issuedTo: document.getElementById('issuedTo').value,
                date: document.getElementById('stockOutDate').value,
                notes: document.getElementById('notes').value,
                recordedBy: 'Admin' // In real app, this would be the logged-in user
            };
            
            // Check if removing this quantity would result in negative stock
            const available = productData[formData.productId].available;
            if (formData.quantity > available) {
                showMessage(`Cannot remove ${formData.quantity} units. Only ${available} available.`, 'error');
                return;
            }
            
            // Show success modal
            showSuccessModal(formData);
        }
    });
    
    // Real-time validation
    const inputs = stockOutForm.querySelectorAll('input, select, textarea');
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
        const requiredFields = ['productSelect', 'quantityRemoved', 'issuedTo', 'stockOutDate'];
        
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
                
                // Check if product has available stock
                if (productData[fieldValue] && productData[fieldValue].available === 0) {
                    field.classList.add('error');
                    errorElement.textContent = 'This product is out of stock';
                    return false;
                }
                break;
                
            case 'quantityRemoved':
                const quantity = parseInt(fieldValue);
                if (isNaN(quantity) || quantity < 1) {
                    field.classList.add('error');
                    errorElement.textContent = 'Quantity must be at least 1';
                    return false;
                }
                
                // Check against available stock
                const selectedProduct = document.getElementById('productSelect').value;
                if (selectedProduct && productData[selectedProduct]) {
                    const available = productData[selectedProduct].available;
                    if (quantity > available) {
                        field.classList.add('error');
                        errorElement.textContent = `Only ${available} units available`;
                        return false;
                    }
                }
                break;
                
            case 'issuedTo':
                if (fieldValue.length < 2) {
                    field.classList.add('error');
                    errorElement.textContent = 'Name must be at least 2 characters';
                    return false;
                }
                break;
                
            case 'stockOutDate':
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
            <h4>Stock Out Details:</h4>
            <p><strong>Product:</strong> ${stockData.productName}</p>
            <p><strong>Quantity Removed:</strong> -${stockData.quantity}</p>
            <p><strong>Issued To:</strong> ${stockData.issuedTo}</p>
            <p><strong>Date:</strong> ${formatDate(stockData.date)}</p>
            <p><strong>Recorded By:</strong> ${stockData.recordedBy}</p>
            ${stockData.notes ? `<p><strong>Notes:</strong> ${stockData.notes}</p>` : ''}
        `;
        
        modal.style.display = 'flex';
        
        // In real app, this would make an API call to save the stock record
        console.log('Stock out data to save:', stockData);
        
        // Update available quantity (for demo purposes)
        if (productData[stockData.productId]) {
            productData[stockData.productId].available -= stockData.quantity;
        }
        
        // Update summary counts
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
        stockOutForm.reset();
        // Reset date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('stockOutDate').value = today;
        
        // Reset quantity input
        const quantityInput = document.getElementById('quantityRemoved');
        quantityInput.max = '';
        quantityInput.disabled = false;
        
        const inputs = stockOutForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
        const errorMessages = stockOutForm.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            msg.textContent = '';
        });
        showMessage('Form has been reset', 'info');
    };
    
    // Record more stock
    window.recordMoreStock = function() {
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
            <td><span class="quantity-badge">-${stockData.quantity}</span></td>
            <td>${stockData.issuedTo}</td>
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
            stockOutForm.dispatchEvent(new Event('submit'));
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
    
    console.log('Stock Out page initialized successfully');
});
