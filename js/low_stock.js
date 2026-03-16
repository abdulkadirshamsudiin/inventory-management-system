/**
 * Low Stock Page JavaScript
 * Handles product filtering, viewing details, and restocking actions
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const urgencyFilter = document.getElementById('urgencyFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const productCards = document.querySelectorAll('.product-card');
    const messageDiv = document.getElementById('message');
    
    // Product data
    const productData = {
        'P004': { name: 'Royco Cubes (pack of 10)', category: 'Food', currentStock: 0, reorderLevel: 25, price: 2.00 },
        'P011': { name: 'Yogurt (500ml)', category: 'Beverages', currentStock: 0, reorderLevel: 10, price: 2.80 },
        'P002': { name: 'Beans (2kg bag)', category: 'Food', currentStock: 8, reorderLevel: 15, price: 18.00 },
        'P007': { name: 'Potatoes (1kg)', category: 'Greens', currentStock: 5, reorderLevel: 10, price: 4.00 },
        'P009': { name: 'Milk (1 liter)', category: 'Beverages', currentStock: 12, reorderLevel: 15, price: 2.50 },
        'P014': { name: 'Toothpaste (100ml)', category: 'Personal Care', currentStock: 8, reorderLevel: 15, price: 2.50 },
        'P017': { name: 'Plastic Bags (roll)', category: 'Household', currentStock: 0, reorderLevel: 20, price: 2.00 },
        'P020': { name: 'Chewing Gum (pack)', category: 'Snacks & Confectionery', currentStock: 12, reorderLevel: 15, price: 0.80 }
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
    
    // Search and filter functionality
    searchInput.addEventListener('input', filterProducts);
    searchBtn.addEventListener('click', filterProducts);
    urgencyFilter.addEventListener('change', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedUrgency = urgencyFilter.value;
        const selectedCategory = categoryFilter.value;
        
        productCards.forEach(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            const cardCategory = card.dataset.category;
            const cardUrgency = card.dataset.urgency;
            
            let matchesSearch = productName.includes(searchTerm);
            let matchesUrgency = !selectedUrgency || cardUrgency === selectedUrgency;
            let matchesCategory = !selectedCategory || cardCategory === selectedCategory;
            
            if (matchesSearch && matchesUrgency && matchesCategory) {
                card.style.display = '';
                card.style.animation = 'fadeIn 0.3s ease';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Product action functions
    window.viewProduct = function(productId) {
        const product = productData[productId];
        if (product) {
            const urgency = product.currentStock === 0 ? 'critical' : 'low';
            const stockPercentage = product.currentStock === 0 ? 0 : Math.round((product.currentStock / product.reorderLevel) * 100);
            
            showProductModal('Product Details', `
                <div class="product-details">
                    <p><strong>Product ID:</strong> ${productId}</p>
                    <p><strong>Product Name:</strong> ${product.name}</p>
                    <p><strong>Category:</strong> ${product.category}</p>
                    <p><strong>Unit Price:</strong> $${product.price.toFixed(2)}</p>
                    <p><strong>Current Stock:</strong> <span class="stock-count ${urgency}">${product.currentStock}</span></p>
                    <p><strong>Reorder Level:</strong> ${product.reorderLevel}</p>
                    <p><strong>Status:</strong> <span class="badge ${urgency}">${urgency === 'critical' ? 'Out of Stock' : 'Low Stock'}</span></p>
                    <p><strong>Stock Level:</strong> ${stockPercentage}% of reorder level</p>
                </div>
            `);
        }
    };
    
    window.restockProduct = function(productId) {
        const product = productData[productId];
        if (product) {
            showRestockModal(productId, product);
        }
    };
    
    // Modal functions
    function showProductModal(title, content) {
        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        
        modalTitle.textContent = title;
        modalBody.innerHTML = content;
        modal.style.display = 'flex';
    }
    
    function showRestockModal(productId, product) {
        const modal = document.getElementById('restockModal');
        document.getElementById('restockProductName').value = product.name;
        document.getElementById('restockCurrentStock').value = product.currentStock;
        document.getElementById('restockQuantity').value = '';
        document.getElementById('restockSupplier').value = '';
        
        // Store product ID for form submission
        document.getElementById('restockForm').dataset.productId = productId;
        
        modal.style.display = 'flex';
        document.getElementById('restockQuantity').focus();
    }
    
    window.closeModal = function() {
        const modal = document.getElementById('productModal');
        modal.style.display = 'none';
    };
    
    window.closeRestockModal = function() {
        const modal = document.getElementById('restockModal');
        modal.style.display = 'none';
    };
    
    // Restock form submission
    document.getElementById('restockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const productId = this.dataset.productId;
        const quantity = parseInt(document.getElementById('restockQuantity').value);
        const supplier = document.getElementById('restockSupplier').value;
        
        if (!quantity || quantity < 1) {
            showMessage('Please enter a valid quantity', 'error');
            return;
        }
        
        if (!supplier || supplier.length < 2) {
            showMessage('Please enter a valid supplier name', 'error');
            return;
        }
        
        // Update product data (for demo purposes)
        if (productData[productId]) {
            productData[productId].currentStock += quantity;
        }
        
        showMessage(`Successfully restocked ${quantity} units of ${productData[productId].name}`, 'success');
        closeRestockModal();
        
        // Update UI after a short delay
        setTimeout(() => {
            location.reload();
        }, 1500);
    });
    
    // Message function
    function showMessage(text, type = 'info') {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        const productModal = document.getElementById('productModal');
        const restockModal = document.getElementById('restockModal');
        
        if (e.target === productModal) {
            closeModal();
        }
        if (e.target === restockModal) {
            closeRestockModal();
        }
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeRestockModal();
        }
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
    });
    
    // Add hover effects to cards
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const indicatorFill = this.querySelector('.indicator-fill');
            if (indicatorFill) {
                indicatorFill.style.transform = 'scaleY(1.2)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const indicatorFill = this.querySelector('.indicator-fill');
            if (indicatorFill) {
                indicatorFill.style.transform = 'scaleY(1)';
            }
        });
    });
    
    // Animate stock indicators on page load
    window.addEventListener('load', function() {
        const indicatorFills = document.querySelectorAll('.indicator-fill');
        indicatorFills.forEach((fill, index) => {
            setTimeout(() => {
                const width = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            }, index * 100);
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
    
    // Auto-refresh simulation (for demo purposes)
    setInterval(function() {
        // Randomly update a stock level to simulate real-time changes
        const productIds = Object.keys(productData);
        const randomId = productIds[Math.floor(Math.random() * productIds.length)];
        const product = productData[randomId];
        
        // Small chance to decrease stock (simulating sales)
        if (Math.random() < 0.1 && product.currentStock > 0) {
            product.currentStock--;
            console.log(`Stock updated: ${product.name} now has ${product.currentStock} units`);
        }
    }, 30000); // Check every 30 seconds
    
    console.log('Low Stock page initialized successfully');
});
