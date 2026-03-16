/**
 * Add Product Page JavaScript
 * Handles sidebar toggle and frontend validation
 * Allows normal PHP form submission when validation passes
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const addProductForm = document.getElementById('addProductForm');
    const messageDiv = document.getElementById('message');

    // Sidebar toggle
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (
                sidebar &&
                sidebarToggle &&
                !sidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target)
            ) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Real-time validation
    const inputs = addProductForm.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        input.addEventListener('blur', function () {
            validateField(this);
        });

        input.addEventListener('input', function () {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });

        // Small input animation
        input.addEventListener('focus', function () {
            if (this.parentElement) {
                this.parentElement.style.transform = 'scale(1.02)';
            }
        });

        input.addEventListener('blur', function () {
            if (this.parentElement) {
                this.parentElement.style.transform = 'scale(1)';
            }
        });
    });

    // Submit form normally if validation passes
    addProductForm.addEventListener('submit', function (e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    function validateForm() {
        let isValid = true;
        const requiredFields = ['productName', 'category', 'unitPrice', 'quantity', 'reorderLevel'];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(field) {
        const fieldName = field.name;
        const fieldValue = field.value.trim();
        const errorElement = document.getElementById(fieldName + 'Error');

        field.classList.remove('error');
        if (errorElement) {
            errorElement.textContent = '';
        }

        if (!fieldValue && field.hasAttribute('required')) {
            field.classList.add('error');
            if (errorElement) {
                errorElement.textContent = 'This field is required';
            }
            return false;
        }

        switch (fieldName) {
            case 'productName': {
                if (fieldValue.length < 3) {
                    field.classList.add('error');
                    if (errorElement) {
                        errorElement.textContent = 'Product name must be at least 3 characters';
                    }
                    return false;
                }
                break;
            }

            case 'unitPrice': {
                const price = parseFloat(fieldValue);
                if (isNaN(price) || price < 0) {
                    field.classList.add('error');
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid price';
                    }
                    return false;
                }
                break;
            }

            case 'quantity': {
                const quantity = parseInt(fieldValue, 10);
                if (isNaN(quantity) || quantity < 0) {
                    field.classList.add('error');
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid quantity';
                    }
                    return false;
                }
                break;
            }

            case 'reorderLevel': {
                const reorderLevel = parseInt(fieldValue, 10);
                if (isNaN(reorderLevel) || reorderLevel < 0) {
                    field.classList.add('error');
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid reorder level';
                    }
                    return false;
                }
                break;
            }

            case 'sku': {
                if (fieldValue && fieldValue.length < 3) {
                    field.classList.add('error');
                    if (errorElement) {
                        errorElement.textContent = 'SKU must be at least 3 characters';
                    }
                    return false;
                }
                break;
            }
        }

        return true;
    }

    // Reset form
    window.resetForm = function () {
        addProductForm.reset();

        inputs.forEach(input => {
            input.classList.remove('error');
        });

        const errorMessages = addProductForm.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            msg.textContent = '';
        });

        showMessage('Form has been reset', 'info');
    };

    function showMessage(text, type = 'info') {
        if (!messageDiv) return;

        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';

        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }

    // Auto-fill SKU visually only
    const productNameInput = document.getElementById('productName');
    if (productNameInput) {
        productNameInput.addEventListener('input', function () {
            const skuField = document.getElementById('sku');
            if (skuField && !skuField.value) {
                const productName = this.value.trim();
                if (productName.length >= 3) {
                    const prefix = productName.substring(0, 3).toUpperCase();
                    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                    skuField.value = `${prefix}${random}`;
                }
            }
        });
    }

    // Keyboard shortcut
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 'Enter') {
            addProductForm.requestSubmit();
        }
    });

    // Resize behavior
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });
});