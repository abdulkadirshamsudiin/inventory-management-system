/**
 * Reports Page JavaScript
 * Handles data visualization, filtering, and export functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const messageDiv = document.getElementById('message');
    
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
    
    // Filter functionality
    const stockCategoryFilter = document.getElementById('stockCategoryFilter');
    const stockInPeriod = document.getElementById('stockInPeriod');
    const stockOutPeriod = document.getElementById('stockOutPeriod');
    
    if (stockCategoryFilter) {
        stockCategoryFilter.addEventListener('change', function() {
            filterTable('current-stock', this.value, 'category');
        });
    }
    
    if (stockInPeriod) {
        stockInPeriod.addEventListener('change', function() {
            filterTableByPeriod('stock-in', this.value);
        });
    }
    
    if (stockOutPeriod) {
        stockOutPeriod.addEventListener('change', function() {
            filterTableByPeriod('stock-out', this.value);
        });
    }
    
    // Filter table by category
    function filterTable(tableId, category, filterType) {
        const table = document.querySelector(`.report-table`);
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            if (category === '') {
                row.style.display = '';
            } else {
                const categoryCell = row.cells[2]; // Category column
                if (categoryCell && categoryCell.textContent.trim() === category) {
                    row.style.display = '';
                    row.style.animation = 'fadeIn 0.3s ease';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    }
    
    // Filter table by period (mock implementation)
    function filterTableByPeriod(tableType, period) {
        showMessage(`Filtering ${tableType} report by ${period}`, 'info');
        
        // In a real application, this would make an API call to get filtered data
        // For demo purposes, we'll just show a message
        
        setTimeout(() => {
            showMessage('Report filtered successfully', 'success');
        }, 1000);
    }
    
    // Export reports functionality
    window.exportReports = function() {
        showMessage('Preparing reports for export...', 'info');
        
        // Simulate export process
        setTimeout(() => {
            // Create a mock CSV content
            const csvContent = generateCSV();
            downloadCSV(csvContent, 'inventory_report.csv');
            showMessage('Reports exported successfully!', 'success');
        }, 1500);
    };
    
    // Generate CSV content
    function generateCSV() {
        let csv = 'Report Type,Date,Product,Quantity,Value\n';
        
        // Add current stock data
        const currentStockTable = document.querySelector('.report-table');
        if (currentStockTable) {
            const rows = currentStockTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                csv += `Current Stock,${cells[0].textContent},${cells[1].textContent},${cells[3].textContent},${cells[5].textContent}\n`;
            }
        }
        
        return csv;
    }
    
    // Download CSV file
    function downloadCSV(content, filename) {
        const blob = new Blob([content], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    // Animate summary cards on page load
    function animateSummaryCards() {
        const cards = document.querySelectorAll('.summary-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.animation = 'slideUp 0.6s ease-out';
            }, index * 100);
        });
    }
    
    // Animate mini charts
    function animateMiniCharts() {
        const chartBars = document.querySelectorAll('.chart-bar');
        chartBars.forEach((bar, index) => {
            const originalHeight = bar.style.height;
            bar.style.height = '0%';
            setTimeout(() => {
                bar.style.height = originalHeight;
            }, index * 50);
        });
    }
    
    // Add hover effects to table rows
    function addTableHoverEffects() {
        const tableRows = document.querySelectorAll('.report-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f0f8ff';
                this.style.transform = 'scale(1.02)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
                this.style.transform = '';
            });
        });
    }
    
    // Calculate and display totals
    function calculateTotals() {
        const tables = document.querySelectorAll('.report-table');
        tables.forEach(table => {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            
            const rows = tbody.getElementsByTagName('tr');
            let totalValue = 0;
            let totalQuantity = 0;
            
            // Determine which columns to sum based on table structure
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                if (cells.length >= 6) {
                    // Sum value column (usually column 5)
                    const valueText = cells[5].textContent.replace(/[$,]/g, '');
                    const value = parseFloat(valueText);
                    if (!isNaN(value)) {
                        totalValue += value;
                    }
                    
                    // Sum quantity column (usually column 3)
                    const quantityText = cells[3].textContent.replace(/[+-,]/g, '');
                    const quantity = parseFloat(quantityText);
                    if (!isNaN(quantity)) {
                        totalQuantity += quantity;
                    }
                }
            }
            
            // Create total row if it doesn't exist
            if (totalValue > 0 || totalQuantity > 0) {
                addTotalRow(table, totalQuantity, totalValue);
            }
        });
    }
    
    // Add total row to table
    function addTotalRow(table, totalQuantity, totalValue) {
        const existingTotalRow = table.querySelector('.total-row');
        if (existingTotalRow) {
            existingTotalRow.remove();
        }
        
        const tbody = table.querySelector('tbody');
        const totalRow = document.createElement('tr');
        totalRow.className = 'total-row';
        totalRow.style.fontWeight = 'bold';
        totalRow.style.backgroundColor = '#f8f9fa';
        totalRow.style.borderTop = '2px solid #dee2e6';
        
        const cellCount = table.querySelector('thead tr').children.length;
        let totalRowHTML = '';
        
        for (let i = 0; i < cellCount; i++) {
            if (i === cellCount - 3) {
                totalRowHTML += `<td>TOTAL</td>`;
            } else if (i === cellCount - 4) {
                totalRowHTML += `<td>${totalQuantity.toLocaleString()}</td>`;
            } else if (i === cellCount - 2) {
                totalRowHTML += `<td>$${totalValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>`;
            } else {
                totalRowHTML += `<td></td>`;
            }
        }
        
        totalRow.innerHTML = totalRowHTML;
        tbody.appendChild(totalRow);
    }
    
    // Message function
    function showMessage(text, type = 'info') {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportReports();
        }
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
    
    // Print functionality
    window.addEventListener('beforeprint', function() {
        // Hide sidebar for printing
        sidebar.style.display = 'none';
        document.querySelector('.main-content').style.marginLeft = '0';
    });
    
    window.addEventListener('afterprint', function() {
        // Restore sidebar after printing
        sidebar.style.display = '';
        document.querySelector('.main-content').style.marginLeft = '';
    });
    
    // Initialize page
    function initializePage() {
        animateSummaryCards();
        animateMiniCharts();
        addTableHoverEffects();
        calculateTotals();
        
        // Add loading animation to charts
        const chartBars = document.querySelectorAll('.chart-bar');
        chartBars.forEach(bar => {
            bar.addEventListener('mouseenter', function() {
                this.style.transform = 'scaleY(1.2)';
            });
            
            bar.addEventListener('mouseleave', function() {
                this.style.transform = 'scaleY(1)';
            });
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
    
    // Auto-refresh simulation (for demo purposes)
    setInterval(function() {
        // Update summary numbers randomly
        const cardNumbers = document.querySelectorAll('.card-number');
        cardNumbers.forEach((element, index) => {
            if (Math.random() < 0.3) { // 30% chance to update
                const currentValue = element.textContent.replace(/[$,]/g, '');
                const change = Math.floor(Math.random() * 10) - 5; // Random change between -5 and 5
                let newValue = parseFloat(currentValue) + change;
                
                if (index === 0) { // Stock value
                    element.textContent = '$' + newValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else { // Other numbers
                    newValue = Math.max(0, newValue); // Don't go below 0
                    element.textContent = newValue.toLocaleString();
                }
            }
        });
    }, 30000); // Update every 30 seconds
    
    // Initialize the page
    initializePage();
    
    console.log('Reports page initialized successfully');
});
