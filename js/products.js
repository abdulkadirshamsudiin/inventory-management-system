/**
 * Products Page JavaScript
 * Handles search, filtering, sidebar toggle, and simple action placeholders
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const productsTable = document.getElementById('productsTable');

    // Sidebar toggle
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // Filter products already displayed in the HTML table
    function filterProducts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : '';

        const rows = productsTable.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.getElementsByTagName('td');

            const productId = cells[0].textContent.toLowerCase();
            const productName = cells[1].textContent.toLowerCase();
            const category = cells[2].textContent.toLowerCase();
            const status = cells[6].textContent.trim().toLowerCase();

            const matchesSearch =
                productId.includes(searchTerm) ||
                productName.includes(searchTerm);

            const matchesCategory =
                selectedCategory === '' ||
                selectedCategory === 'all categories' ||
                category === selectedCategory;

            const matchesStatus =
                selectedStatus === '' ||
                selectedStatus === 'all status' ||
                status === selectedStatus;

            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', filterProducts);
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterProducts);
    }
});

// Simple placeholder functions for action buttons
function viewProduct(productId) {
    alert("View product: " + productId);
}

function editProduct(productId) {
    alert("Edit product: " + productId);
}

function deleteProduct(productId) {
    const confirmDelete = confirm("Are you sure you want to delete product " + productId + "?");
    if (confirmDelete) {
        alert("Delete function for " + productId + " will be connected to the database later.");
    }
}