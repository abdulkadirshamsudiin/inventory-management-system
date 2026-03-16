# Inventory Management System (South C)

A comprehensive web-based inventory management solution specifically designed for Small and Medium Enterprises (SMEs) in South C. This system provides complete inventory tracking, user management, and reporting capabilities with a modern, user-friendly interface.

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [System Requirements](#system-requirements)
3. [Installation Guide](#installation-guide)
4. [Project Structure](#project-structure)
5. [Database Schema](#database-schema)
6. [Page Navigation & Connections](#page-navigation--connections)
7. [Feature Documentation](#feature-documentation)
8. [Security Features](#security-features)
9. [User Guide](#user-guide)
10. [Technical Implementation](#technical-implementation)
11. [Customization & Extension](#customization--extension)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 Project Overview

The Inventory Management System is a full-stack web application built with PHP, MySQL, HTML/CSS, and JavaScript. It provides essential inventory management features including:

- **User Authentication**: Secure login/logout system with role management
- **Product Management**: Add, edit, delete, and search products
- **Stock Tracking**: Real-time inventory monitoring with low stock alerts
- **Transaction Recording**: Stock in/out management with supplier/customer tracking
- **Reporting**: Comprehensive reports with filtering and export capabilities
- **Dashboard**: Real-time overview with statistics and quick actions

### Target Audience
- Small and Medium Enterprises (SMEs)
- Retail businesses
- Warehouse management
- Inventory-based businesses in South C

---

## ⚙️ System Requirements

### Server Requirements
- **Web Server**: Apache (recommended) or Nginx
- **PHP Version**: PHP 7.4 or higher
- **Database**: MySQL 5.7 or higher / MariaDB 10.2 or higher
- **PHP Extensions**:
  - mysqli
  - session
  - json (for JavaScript functionality)

### Client Requirements
- **Web Browser**: Modern browser (Chrome, Firefox, Safari, Edge)
- **JavaScript**: Enabled
- **CSS**: Supported

---

## 🚀 Installation Guide

### Step 1: Server Setup
1. Install XAMPP, WAMP, or similar web server package
2. Ensure Apache and MySQL services are running
3. Place the project folder in the web root (`htdocs` for XAMPP)

### Step 2: Database Configuration
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `inventory_system`
3. Execute the following SQL queries to create tables:

```sql
-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category ENUM('Food', 'Greens', 'Beverages', 'Personal Care', 'Household', 'Snacks & Confectionery') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Stock In Table
CREATE TABLE stock_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    supplier VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    recorded_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Stock Out Table
CREATE TABLE stock_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    issued_to VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    recorded_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### Step 3: Configuration
1. Open `includes/db_connection.php`
2. Verify database credentials match your setup:
   ```php
   $host = "localhost";
   $username = "root";
   $password = ""; // Your MySQL password
   $database = "inventory_system";
   ```

### Step 4: Access the Application
1. Open your web browser
2. Navigate to `http://localhost/Inventory%20Management%20System%20(South%20C)/`
3. You will be redirected to the login page

---

## 📁 Project Structure

```
Inventory Management System (South C)/
│
├── 📄 index.php                    # Main entry point (redirects to login)
│
├── 📁 includes/                    # Core configuration files
│   └── 📄 db_connection.php       # Database connection configuration
│
├── 📁 pages/                       # All application pages
│   ├── 📄 login.php               # User authentication
│   ├── 📄 signup.php              # User registration
│   ├── 📄 logout.php              # User logout
│   ├── 📄 dashboard.php           # Main dashboard
│   ├── 📄 products.php            # Product listing
│   ├── 📄 add_product.php         # Add new product
│   ├── 📄 edit_product.php        # Edit existing product
│   ├── 📄 delete_product.php      # Delete product
│   ├── 📄 stock_in.php            # Record stock in
│   ├── 📄 stock_out.php           # Record stock out
│   ├── 📄 low_stock.php           # Low stock alerts
│   └── 📄 reports.php             # Generate reports
│
├── 📁 css/                         # Stylesheets (9 files)
│   ├── 📄 login.css
│   ├── 📄 dashboard.css
│   ├── 📄 products.css
│   ├── 📄 add_product.css
│   ├── 📄 stock_in.css
│   ├── 📄 stock_out.css
│   ├── 📄 low_stock.css
│   ├── 📄 reports.css
│   └── 📄 logout.css
│
└── 📁 js/                          # JavaScript files (9 files)
    ├── 📄 login.js
    ├── 📄 dashboard.js
    ├── 📄 products.js
    ├── 📄 add_product.js
    ├── 📄 stock_in.js
    ├── 📄 stock_out.js
    ├── 📄 low_stock.js
    ├── 📄 reports.js
    └── 📄 logout.js
```

---

## 🗄️ Database Schema

### Users Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| username | VARCHAR(50) UNIQUE | Login username |
| password | VARCHAR(255) | User password |
| full_name | VARCHAR(100) | Display name |
| role | ENUM('admin','user') | User role |

### Products Table
| Column | Type | Description |
|--------|------|-------------|
| id | VARCHAR(10) | Product ID (e.g., P001) |
| name | VARCHAR(200) | Product name |
| category | ENUM | Product category |
| price | DECIMAL(10,2) | Unit price |
| quantity | INT | Current stock |
| reorder_level | INT | Alert threshold |

### Stock In Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| product_id | VARCHAR(10) | Foreign key to products |
| quantity | INT | Quantity added |
| supplier | VARCHAR(200) | Supplier name |
| date | DATE | Transaction date |
| notes | TEXT | Additional notes |
| recorded_by | VARCHAR(50) | User who recorded |

### Stock Out Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| product_id | VARCHAR(10) | Foreign key to products |
| quantity | INT | Quantity removed |
| issued_to | VARCHAR(200) | Customer/recipient |
| date | DATE | Transaction date |
| notes | TEXT | Reason for removal |
| recorded_by | VARCHAR(50) | User who recorded |

---

## 🔗 Page Navigation & Connections

### Navigation Flow Diagram

```
┌─────────────────┐
│   index.php     │
│  (Entry Point)  │
└─────────┬───────┘
          │ redirects to
          ▼
┌─────────────────┐
│   login.php     │ ◄─────────────────┐
│  (Authentication)│                  │
└─────────┬───────┘                  │
          │ successful login         │
          ▼                          │ signup
┌─────────────────┐                  │
│  dashboard.php  │                  │
│  (Main Hub)     │──────────────────┘
└─────────┬───────┘
          │
    ┌─────┴─────┬─────────┬─────────┬─────────┬─────────┐
    │           │         │         │         │         │
    ▼           ▼         ▼         ▼         ▼         ▼
┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐
│products │ │add_prod │ │stock_in │ │stock_out│ │low_stock│ │ reports │
│  .php   │ │uct.php  │ │  .php   │ │  .php   │ │  .php   │ │  .php   │
└─────┬───┘ └─────┬───┘ └─────┬───┘ └─────┬───┘ └─────┬───┘ └─────┬───┘
      │           │         │         │         │         │
      │ edit      │         │         │         │         │
      ▼           ▼         │         │         │         │
┌─────────┐         │         │         │         │         │
│edit_prod│         │         │         │         │         │
│uct.php  │         │         │         │         │         │
└─────┬───┘         │         │         │         │         │
      │ delete      │         │         │         │         │
      ▼             │         │         │         │         │
┌─────────┐         │         │         │         │         │
│delete_  │         │         │         │         │         │
│product  │         │         │         │         │         │
│.php     │         │         │         │         │         │
└─────────┘         │         │         │         │         │
                     │         │         │         │         │
                     ▼         ▼         ▼         ▼         ▼
               ┌─────────────────────────────────────────────┐
               │           logout.php                        │
               │         (Session End)                      │
               └─────────────────────────────────────────────┘
```

### Detailed Page Connections

#### **1. Entry Point & Authentication**

**index.php → login.php**
- **Connection**: Automatic redirect via `header('Location: pages/login.php')`
- **Purpose**: Entry point routes to authentication

**login.php ↔ signup.php**
- **Connection**: Bidirectional links
- **login.php**: "Don't have an account? Sign up here" → signup.php
- **signup.php**: "Already have an account? Login here" → login.php
- **Purpose**: User registration and authentication flow

**login.php → dashboard.php**
- **Connection**: Successful login redirect via `header("Location: dashboard.php")`
- **Purpose**: Main application entry after authentication

#### **2. Dashboard Navigation Hub**

**dashboard.php** serves as the central navigation hub with sidebar links:

| Navigation Link | Target Page | Purpose |
|------------------|-------------|---------|
| 📊 Dashboard | dashboard.php | Current page (refresh) |
| 📦 Products | products.php | View all products |
| ➕ Add Product | add_product.php | Add new product |
| 📥 Stock In | stock_in.php | Record incoming stock |
| 📤 Stock Out | stock_out.php | Record outgoing stock |
| ⚠️ Low Stock | low_stock.php | View low stock alerts |
| 📈 Reports | reports.php | Generate reports |
| 🚪 Logout | logout.php | End session |

#### **3. Product Management Flow**

**products.php → add_product.php**
- **Connection**: "➕ Add Product" button
- **Purpose**: Create new products

**products.php → edit_product.php**
- **Connection**: Edit button (✏️) for each product
- **URL Pattern**: `edit_product.php?id={product_id}`
- **Purpose**: Modify existing product details

**products.php → delete_product.php**
- **Connection**: Delete button (🗑️) with confirmation
- **URL Pattern**: `delete_product.php?id={product_id}`
- **Purpose**: Remove products from inventory

**add_product.php → products.php**
- **Connection**: Successful product creation redirect
- **Purpose**: Return to product list

**edit_product.php → products.php**
- **Connection**: "← Back to Products" link and successful update redirect
- **Purpose**: Return to product list after editing

**delete_product.php → products.php**
- **Connection**: Automatic redirect after deletion
- **Purpose**: Return to updated product list

#### **4. Stock Management Flow**

**products.php → stock_in.php**
- **Connection**: Quick action button and sidebar navigation
- **Purpose**: Add inventory to products

**products.php → stock_out.php**
- **Connection**: Quick action button and sidebar navigation
- **Purpose**: Remove inventory from products

**stock_in.php → products.php**
- **Connection**: Successful stock in recording redirect
- **Purpose**: Return to product list with updated quantities

**stock_out.php → products.php**
- **Connection**: Successful stock out recording redirect
- **Purpose**: Return to product list with updated quantities

#### **5. Low Stock Management Flow**

**low_stock.php → stock_in.php**
- **Connection**: "🔄 Restock Now" buttons for each product
- **Purpose**: Quick restocking from low stock alerts

**dashboard.php → low_stock.php**
- **Connection**: Low stock summary card and sidebar navigation
- **Purpose**: View detailed low stock information

#### **6. Reporting Flow**

**dashboard.php → reports.php**
- **Connection**: "📈 Reports" quick action button and sidebar navigation
- **Purpose**: Access comprehensive reports

**reports.php** has internal filtering:
- Category filtering for current stock
- Period filtering for stock in/out reports
- Export functionality (placeholder)

#### **7. Session Management Flow**

**All authenticated pages → logout.php**
- **Connection**: Sidebar "🚪 Logout" link
- **Purpose**: Secure session termination

**logout.php → login.php**
- **Connection**: Auto-redirect after 5 seconds and manual link
- **Purpose**: Return to authentication

---

## 📚 Feature Documentation

### 1. User Authentication System

#### **Login Process**
1. User enters username and password
2. System validates against `users` table
3. Session variables created:
   - `$_SESSION['user']` - Username
   - `$_SESSION['full_name']` - Display name
   - `$_SESSION['role']` - User role
4. Redirect to dashboard

#### **Signup Process**
1. User fills registration form
2. System validates:
   - Password confirmation match
   - Username uniqueness
3. Creates new user with default "user" role
4. Redirect to login page

#### **Security Features**
- Session-based authentication
- Prepared statements for SQL injection prevention
- Input validation and sanitization
- Session destruction on logout

### 2. Product Management

#### **Product Categories**
- Food
- Greens
- Beverages
- Personal Care
- Household
- Snacks & Confectionery

#### **Product ID Generation**
- Automatic format: P001, P002, P003...
- Based on last product ID in database
- Padded with zeros for consistent formatting

#### **Stock Status Logic**
```php
if ($quantity == 0) {
    $status = "Out of Stock";
    $class = "out-of-stock";
} elseif ($quantity <= $reorder_level) {
    $status = "Low Stock";
    $class = "low-stock";
} else {
    $status = "In Stock";
    $class = "in-stock";
}
```

### 3. Stock Management

#### **Stock In Process**
1. Select product from dropdown
2. Enter quantity and supplier details
3. System:
   - Inserts record into `stock_in` table
   - Updates product quantity: `quantity = quantity + added_amount`
4. Redirect to products page

#### **Stock Out Process**
1. Select product (shows current stock)
2. Enter quantity and recipient details
3. System validates sufficient stock
4. System:
   - Inserts record into `stock_out` table
   - Updates product quantity: `quantity = quantity - removed_amount`
5. Redirect to products page

#### **Low Stock Alerts**
- **Critical**: Quantity = 0
- **Low Stock**: 0 < Quantity ≤ Reorder Level
- Visual indicators and alerts on dashboard and low stock page

### 4. Dashboard Statistics

#### **Real-time Calculations**
```php
// Total Products
SELECT COUNT(*) FROM products

// Total Stock Value
SELECT SUM(price * quantity) FROM products

// Low Stock Items
SELECT COUNT(*) FROM products WHERE quantity <= reorder_level

// Recent Transactions
SELECT (SELECT COUNT(*) FROM stock_in) + (SELECT COUNT(*) FROM stock_out)
```

#### **Recent Activity**
- Combines stock in and stock out records
- Ordered by date (most recent first)
- Shows last 10 transactions

### 5. Reporting System

#### **Current Stock Report**
- All products with current quantities
- Total value calculation (price × quantity)
- Category filtering
- Status indicators

#### **Stock In Report**
- All incoming stock records
- Cost calculations
- Supplier information
- Period filtering options

#### **Stock Out Report**
- All outgoing stock records
- Value calculations
- Customer/recipient information
- Period filtering options

---

## 🛡️ Security Features

### 1. Database Security
- **Prepared Statements**: All SQL queries use parameterized statements
- **Input Validation**: Form inputs are validated and sanitized
- **XSS Prevention**: `htmlspecialchars()` for output escaping
- **SQL Injection Prevention**: MySQLi prepared statements

### 2. Session Security
- **Session Management**: Proper session start and destruction
- **Authentication Check**: Session validation on protected pages
- **Secure Logout**: Complete session destruction

### 3. Input Validation
- **Required Fields**: Form validation for mandatory inputs
- **Data Type Validation**: Numeric validation for prices and quantities
- **Length Validation**: Appropriate field length limits
- **Business Logic Validation**: Stock quantity checks, etc.

---

## 👥 User Guide

### 1. Getting Started

#### **First-Time Setup**
1. Access the system via web browser
2. Click "Sign up here" to create an account
3. Fill in registration form
4. Login with new credentials

#### **Daily Operations**
1. Login to system
2. View dashboard for overview
3. Check low stock alerts
4. Record stock movements
5. Generate reports as needed

### 2. Product Management

#### **Adding Products**
1. Navigate to Products → Add Product
2. Fill in product details:
   - Product name (required)
   - Category (required)
   - Unit price (required)
   - Initial quantity (required)
   - Reorder level (required)
   - Description (optional)
3. Click "Add Product"

#### **Editing Products**
1. Go to Products page
2. Click edit button (✏️) next to product
3. Modify desired fields
4. Click "Save Changes"

#### **Deleting Products**
1. Go to Products page
2. Click delete button (🗑️)
3. Confirm deletion in popup

### 3. Stock Management

#### **Recording Stock In**
1. Navigate to Stock In
2. Select product from dropdown
3. Enter:
   - Quantity added
   - Supplier name
   - Date (defaults to today)
   - Notes (optional)
4. Click "Record Stock In"

#### **Recording Stock Out**
1. Navigate to Stock Out
2. Select product (shows current stock)
3. Enter:
   - Quantity removed
   - Issued to/customer
   - Date (defaults to today)
   - Notes (optional)
4. Click "Record Stock Out"

### 4. Monitoring & Reports

#### **Checking Low Stock**
1. Dashboard shows low stock count
2. Click "Low Stock" for detailed view
3. Use filters to find specific products
4. Click "Restock Now" to add inventory

#### **Generating Reports**
1. Navigate to Reports
2. View different report sections:
   - Current Stock Report
   - Stock In Report
   - Stock Out Report
3. Use filters to narrow data
4. Export reports (when implemented)

---

## 💻 Technical Implementation

### 1. Architecture Pattern

#### **MVC-like Structure**
- **Models**: Database operations in PHP files
- **Views**: HTML templates with embedded PHP
- **Controllers**: PHP files handling business logic

#### **File Organization**
- **Separation of Concerns**: CSS, JS, PHP in separate directories
- **Modular Design**: Each page has dedicated CSS and JS files
- **Reusable Components**: Sidebar, header, and modal patterns

### 2. Database Design

#### **Relationships**
- **One-to-Many**: Products → Stock In/Out records
- **Foreign Keys**: Maintain data integrity
- **Indexes**: Optimized for common queries

#### **Data Integrity**
- **Referential Integrity**: Foreign key constraints
- **Data Types**: Appropriate types for each field
- **Default Values**: Sensible defaults where applicable

### 3. Frontend Technology

#### **CSS Architecture**
- **Modular CSS**: Separate files for each page
- **Consistent Design**: Shared patterns and variables
- **Responsive Design**: Mobile-friendly layouts
- **Modern Styling**: Gradients, shadows, animations

#### **JavaScript Features**
- **DOM Manipulation**: Dynamic content updates
- **Form Validation**: Client-side validation
- **Interactive Elements**: Modals, toggles, filters
- **AJAX Ready**: Structure supports future AJAX implementation

### 4. Backend Technology

#### **PHP Features Used**
- **Session Management**: User authentication
- **MySQLi**: Database operations
- **Prepared Statements**: Security
- **Header Redirects**: Navigation flow
- **Error Handling**: Graceful error management

#### **Security Implementation**
- **Input Sanitization**: `trim()`, `htmlspecialchars()`
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Proper session handling
- **Access Control**: Role-based permissions (framework for future)

---

## 🔧 Customization & Extension

### 1. Easy Customizations

#### **Adding New Categories**
1. Open `add_product.php`
2. Add new option to category select:
   ```html
   <option value="New Category">New Category</option>
   ```
3. Update database ENUM if needed
4. Update all category select elements across pages

#### **Changing Color Scheme**
1. Edit CSS files in `/css/` directory
2. Main color variables in each CSS file:
   ```css
   .sidebar {
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   }
   ```

#### **Modifying Business Logic**
1. Update calculations in relevant PHP files
2. Example: Change reorder level logic in `products.php`

### 2. Advanced Extensions

#### **Adding New Features**
1. Create new PHP page in `/pages/`
2. Create corresponding CSS file in `/css/`
3. Create JavaScript file in `/js/`
4. Add navigation links to sidebar
5. Update database schema if needed

#### **User Role Management**
1. Extend `users` table with permissions
2. Implement access control in each page:
   ```php
   if ($_SESSION['role'] !== 'admin') {
       header('Location: dashboard.php');
       exit();
   }
   ```

#### **API Integration**
1. Create API endpoints
2. Implement AJAX calls from JavaScript
3. Add proper error handling and validation

### 3. Database Extensions

#### **Adding New Tables**
1. Design table structure
2. Create via phpMyAdmin or SQL script
3. Update PHP files to use new tables
4. Add foreign key relationships as needed

#### **Adding New Fields**
1. Alter existing tables:
   ```sql
   ALTER TABLE products ADD COLUMN new_field VARCHAR(100);
   ```
2. Update PHP forms to include new fields
3. Modify database queries accordingly

---

## 🐛 Troubleshooting

### 1. Common Issues

#### **Database Connection Errors**
- **Problem**: "Database connection failed"
- **Solution**: 
  - Check MySQL service is running
  - Verify credentials in `db_connection.php`
  - Ensure database exists

#### **Login Issues**
- **Problem**: Can't login with correct credentials
- **Solution**:
  - Check if user exists in database
  - Verify password matching
  - Check session configuration

#### **Page Not Found Errors**
- **Problem**: 404 errors when navigating
- **Solution**:
  - Check file paths in links
  - Verify .htaccess configuration
  - Ensure correct directory permissions

#### **CSS/JS Not Loading**
- **Problem**: Unstyled pages or non-functional JavaScript
- **Solution**:
  - Check file paths in HTML head sections
  - Verify file permissions
  - Check browser developer console for errors

### 2. Debugging Tips

#### **Enable Error Reporting**
Add to PHP files for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

#### **Database Query Debugging**
```php
$result = $conn->query($sql);
if (!$result) {
    echo "Error: " . $conn->error;
}
```

#### **Session Debugging**
```php
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
```

### 3. Performance Optimization

#### **Database Optimization**
- Add indexes to frequently queried columns
- Use LIMIT clauses for large result sets
- Optimize JOIN queries

#### **Frontend Optimization**
- Minify CSS and JavaScript files
- Implement caching headers
- Optimize images and assets

---

## 📞 Support & Maintenance

### 1. Regular Maintenance Tasks

#### **Database Maintenance**
- Regular backups
- Optimize tables
- Monitor storage usage
- Clean old transaction logs

#### **Application Updates**
- Security patches
- Feature updates
- Bug fixes
- Performance improvements

### 2. Backup Strategy

#### **Database Backup**
```bash
mysqldump -u root -p inventory_system > backup.sql
```

#### **File Backup**
- Regular backups of all PHP files
- Version control (Git recommended)
- Document configuration changes

### 3. Security Considerations

#### **Regular Security Checks**
- Update PHP version
- Review user permissions
- Monitor access logs
- Implement HTTPS (SSL certificate)

---

## 📄 License & Credits

### License
This project is open-source and available under the MIT License.

### Credits
- Designed and developed for SMEs in South C
- Built with PHP, MySQL, HTML, CSS, and JavaScript
- Responsive design for modern browsers

---

## 🔄 Version History

### Version 1.0.0
- Initial release
- Core inventory management features
- User authentication system
- Basic reporting capabilities
- Responsive design implementation

---

**For technical support or questions about this Inventory Management System, please refer to the troubleshooting section or contact your system administrator.**

*Last Updated: March 2026*
