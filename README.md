# 📦 Inventory Management System (South C)

Developer-level technical documentation for the PHP/MySQL inventory platform in this repository.

---

## 📋 Table of Contents

1. [Project Overview](#-project-overview)
2. [System Purpose and Users](#-system-purpose-and-users)
3. [Architecture Summary](#-architecture-summary)
4. [Runtime Request Flow](#-runtime-request-flow)
5. [Full Project Structure](#-full-project-structure)
6. [Database Design](#-database-design)
7. [Authentication and Session System](#-authentication-and-session-system)
8. [Feature-by-Feature Internal Breakdown](#-feature-by-feature-internal-breakdown)
9. [Navigation, Redirects, and Page Flow](#-navigation-redirects-and-page-flow)
10. [Security Implementation](#-security-implementation)
11. [Where Important Code Lives](#-where-important-code-lives)
12. [Setup and Local Development](#-setup-and-local-development)
13. [Known Weaknesses and Improvement Opportunities](#-known-weaknesses-and-improvement-opportunities)

---

## 🎯 Project Overview

This project is a web-based inventory management system built with:

- `PHP` for request handling, session management, validation, and database operations
- `MySQL` for persistent storage
- `HTML + CSS` for server-rendered UI
- `Vanilla JavaScript` for client-side validation, filtering, modals, export, and responsive behaviors

At runtime, the application allows authenticated users to:

- create accounts and log in
- view a dashboard with stock summaries and recent activity
- manage products
- record stock-in and stock-out transactions
- monitor low-stock items
- generate inventory reports
- optionally authenticate with Google OAuth

This is not an API-first system. It is a classic server-rendered PHP application where each page is both:

- a controller handling form submission and database work
- a view rendering the HTML response

---

## 🧭 System Purpose and Users

### What the system does

The system tracks product inventory levels and the movement of stock over time. It stores a product catalog, records inbound stock (`stock_in`), records outbound stock (`stock_out`), and derives operational views such as low-stock alerts, dashboard counters, and printable/exportable reports.

### Real purpose of the system

The real purpose is operational stock control for a small or medium business. The system is designed to answer practical questions such as:

- What products do we currently have?
- Which items are running low?
- What stock came in today or this week?
- What stock was issued out, to whom, and by whom?
- What is the current value of inventory on hand?

### Target users

The codebase supports two user roles:

- `admin`
  - can add, edit, and delete products
  - can record stock-in and stock-out transactions
  - sees admin-only navigation items
- `user`
  - can log in
  - can view dashboard, products, low-stock alerts, and reports
  - cannot access admin-only workflows

Operationally, the intended users appear to be:

- store managers
- stock clerks
- warehouse or back-office staff
- small business owners

---

## 🏗️ Architecture Summary

### Architecture style

This application is best described as a **procedural PHP application with a shared service/helper layer**.

It is **not strict MVC**.

Why:

- page files under `pages/` handle request validation, business rules, SQL, redirects, and HTML rendering in the same file
- shared reusable behavior is centralized in `includes/app.php`
- database connection setup is isolated in `includes/db_connection.php`
- frontend assets are separated by page in `css/` and `js/`

### Separation of responsibilities

#### Shared infrastructure

- `includes/db_connection.php`
  - opens the MySQL connection
- `includes/app.php`
  - starts sessions
  - defines auth helpers
  - defines CSRF helpers
  - defines shared UI helpers
  - defines reusable domain helpers such as product status and next product ID generation
- `includes/google_oauth.php`
  - encapsulates Google OAuth configuration and HTTP calls

#### Route/controller/view pages

- `pages/*.php`
  - each page is an entry point
  - processes GET/POST input
  - executes SQL
  - may redirect
  - renders final HTML

#### Presentation assets

- `css/*.css`
  - page-specific styling
- `js/*.js`
  - page-specific interactivity and validation

### Strengths

- easy to understand for small teams
- minimal indirection
- no framework overhead
- shared helpers reduce duplication for auth/session/flash/sidebar concerns
- server-rendered pages work without complex frontend tooling

### Weaknesses

- business logic is distributed across page files instead of domain/service classes
- no migration system in the repository
- no automated tests
- no routing layer or controller abstraction
- SQL is partly centralized and partly embedded per page
- some security and configuration concerns are still handled manually

---

## 🔄 Runtime Request Flow

This section explains how data moves through the system from browser action to database update and back to the user.

### General pattern

```text
User Action
  -> Browser requests PHP page
  -> Page includes includes/app.php
  -> app.php starts session + loads DB connection
  -> Page checks access control
  -> If POST: validate CSRF + validate inputs
  -> Execute SQL queries / transactions
  -> Set flash message if needed
  -> Redirect or render HTML
  -> Browser loads related CSS/JS assets
```

### Form submission lifecycle

The common form lifecycle across login, signup, add product, stock in, stock out, and delete flows is:

1. A PHP page renders an HTML form.
2. The form includes a hidden CSRF token using `render_csrf_field()`.
3. Client-side JavaScript performs convenience validation before submit.
4. The browser submits the form back to the same PHP page or to a target action page.
5. The PHP page checks `is_post_request()`.
6. The submitted CSRF token is validated using `verify_csrf_token()`.
7. Inputs are trimmed, normalized, and validated on the server.
8. SQL statements execute through MySQLi prepared statements.
9. On success, the page usually sets a flash message and redirects with `header('Location: ...')`.
10. On the next page load, `render_flash()` displays the one-time message and clears it from the session.

### Read-only page lifecycle

Dashboard, products, low-stock, and reports pages mostly follow this pattern:

1. User requests the page.
2. Page includes `app.php`.
3. Access control is checked with `require_login()` or `require_admin()`.
4. Page performs one or more `SELECT` queries.
5. PHP loops through result sets and renders HTML tables/cards.
6. Page-specific JavaScript enhances filtering, counters, modals, or export.

### Redirect behavior

Redirects are a core part of the application design:

- unauthenticated user requesting protected page
  - redirected to `login.php`
- already logged-in user opening `login.php` or `signup.php`
  - redirected to `dashboard.php`
- successful login
  - redirected to `dashboard.php`
- successful signup
  - redirected to `login.php`
- failed authorization for admin-only page
  - redirected to `dashboard.php`
- successful add/edit/delete product
  - redirected to `products.php`
- successful stock-in/stock-out
  - redirected back to the same transaction page
- logout
  - ends session, renders confirmation page, then JS redirects to landing page

### Data processing examples

#### Example: stock-in flow

```text
Admin opens stock_in.php
  -> Page loads product dropdown from products table
Admin submits form
  -> verify_csrf_token()
  -> Validate product, quantity, supplier, date
  -> BEGIN TRANSACTION
  -> INSERT INTO stock_in (...)
  -> UPDATE products SET quantity = quantity + ?
  -> COMMIT
  -> Flash success
  -> Redirect to stock_in.php
  -> History table re-renders with the new transaction included
```

#### Example: stock-out flow

```text
Admin opens stock_out.php
  -> Page loads products and current quantities
Admin submits form
  -> verify_csrf_token()
  -> Validate product, quantity, recipient, date
  -> Confirm available quantity
  -> BEGIN TRANSACTION
  -> UPDATE products SET quantity = quantity - ? WHERE quantity >= ?
  -> INSERT INTO stock_out (...)
  -> COMMIT
  -> Flash success
  -> Redirect to stock_out.php
```

---

## 📁 Full Project Structure

### Top level

```text
Inventoy Management System (South C)/
├── index.php
├── README.md
├── css/
├── includes/
├── js/
└── pages/
```

> Note: `.git/` exists as Git repository metadata. It is version-control infrastructure, not application runtime logic, so it is intentionally not documented file-by-file here.

### Root files

| Path | Purpose | Internal role | Connections |
|---|---|---|---|
| `index.php` | Public landing page / entry point | Includes shared app bootstrap, checks whether a user is already logged in, and changes CTA links accordingly | Uses `includes/app.php`; links to `pages/signup.php`, `pages/login.php`, and `pages/dashboard.php`; styled by `css/landing.css` |
| `README.md` | Developer documentation | This file | Documents the whole repository |

### `includes/` directory

This folder contains shared runtime code that almost every page depends on.

| File | What it does | Why it exists | How it connects |
|---|---|---|---|
| `includes/app.php` | Core bootstrap/helper layer. Starts the session, loads DB connection, defines auth helpers, flash messaging, CSRF helpers, user lookup functions, Google account resolution, HTML escaping, sidebar/header rendering, product categories, stock status logic, and product ID generation. | Prevents every page from duplicating session logic, security helpers, and common UI fragments. | Required by `index.php` and all files in `pages/`. Internally requires `db_connection.php`. |
| `includes/db_connection.php` | Creates the global MySQLi connection `$conn`, enables strict MySQLi error reporting, and sets UTF-8 encoding. | Centralizes DB configuration so the whole app uses one connection setup. | Required by `includes/app.php`; all SQL in page files uses `$conn` from this file. |
| `includes/google_oauth.php` | Encapsulates Google OAuth configuration, authorize URL generation, token exchange, user info fetch, and low-level HTTP request logic. | Keeps OAuth protocol details out of page files. | Used by `pages/google_start.php`, `pages/google_callback.php`, and indirectly by auth-related helpers in `includes/app.php`. |
| `includes/google_oauth_config.local.example.php` | Example local override file for Google OAuth secrets. | Documents expected config structure without real credentials. | Used as a template for the real local config. |
| `includes/google_oauth_config.local.php` | Local Google OAuth override file loaded by `google_oauth_config()`. | Supplies client ID, client secret, and redirect URI for Google sign-in. | Read by `includes/google_oauth.php`. This file currently contains real credentials and should not be committed. |

### `pages/` directory

This directory contains the actual web routes.

| File | What it does | Core logic inside | Connections |
|---|---|---|---|
| `pages/login.php` | Local account sign-in page | Redirects logged-in users away, validates POST input, verifies CSRF, calls `authenticate_user()`, calls `login_user()`, sets flash messages, renders login form, exposes Google sign-in link | Uses `includes/app.php`; links to `signup.php`, `dashboard.php`, and `google_start.php`; styled by `css/login.css`; enhanced by `js/login.js` |
| `pages/signup.php` | Local account registration page | Redirects logged-in users away, validates name/email/password fields, ensures unique email, hashes password with `password_hash()`, inserts new user, forces public signups to role `user`, renders Google sign-up link | Uses `includes/app.php` and `includes/google_oauth.php`; redirects to `login.php`; styled by `css/login.css`; enhanced by `js/login.js` |
| `pages/logout.php` | Session termination page | Checks whether user was logged in, calls `logout_user()`, renders a status page, then relies on JS to redirect to landing page | Uses `includes/app.php`; styled by `css/logout.css`; enhanced by `js/logout.js` |
| `pages/dashboard.php` | Main overview page after login | Enforces login, calculates summary metrics from `products`, `stock_in`, and `stock_out`, builds a UNION activity feed, renders quick links | Uses shared sidebar/header helpers; styled by `css/dashboard.css` and `css/sidebar-shell.css`; enhanced by `js/dashboard.js` |
| `pages/products.php` | Product list page | Enforces login, reads all products, computes stock status per row, renders action buttons, renders admin-only edit/delete actions and a details modal | Uses `product_status()` and role checks from `app.php`; styled by `css/products.css` and `css/sidebar-shell.css`; enhanced by `js/products.js` |
| `pages/add_product.php` | Product creation page | Enforces admin role, validates product inputs, prevents duplicate names, generates next ID with `next_product_id()`, inserts into `products`, redirects on success | Uses `product_categories()` and CSRF helpers from `app.php`; styled by `css/add_product.css` and `css/sidebar-shell.css`; enhanced by `js/add_product.js` |
| `pages/edit_product.php` | Product update page | Enforces admin role, loads product by `id`, validates edits, prevents duplicate names against other products, updates the row | Uses shared helpers and the same styling as add product; linked from `products.php` |
| `pages/delete_product.php` | Product deletion endpoint | Enforces admin role, only accepts POST, verifies CSRF, deletes product by `id`, sets success/error flash, redirects to products page | Called by inline delete form in `products.php` |
| `pages/stock_in.php` | Inbound inventory transaction page | Enforces admin role, validates form input, confirms product exists, opens DB transaction, inserts a `stock_in` record, increments `products.quantity`, calculates today/week totals, renders history table | Styled by `css/stock_in.css` and `css/sidebar-shell.css`; enhanced by `js/stock_in.js` |
| `pages/stock_out.php` | Outbound inventory transaction page | Enforces admin role, validates form input, checks available quantity, runs transaction-safe decrement first, inserts `stock_out` record, calculates today/week totals, renders history table | Styled by `css/stock_out.css` and `css/sidebar-shell.css`; enhanced by `js/stock_out.js` |
| `pages/low_stock.php` | Low stock monitoring page | Enforces login, computes critical vs low counts, queries all products at or below reorder level, renders urgency cards and details modal | Uses `products` only; styled by `css/low_stock.css` and `css/sidebar-shell.css`; enhanced by `js/low_stock.js` |
| `pages/reports.php` | Reporting and export page | Enforces login, calculates top-level totals, loads current stock report, stock-in report, and stock-out report, renders export button | Styled by `css/reports.css` and `css/sidebar-shell.css`; enhanced by `js/reports.js` |
| `pages/google_start.php` | Google OAuth entry point | Validates OAuth config, stores CSRF-like state token and origin in session, redirects to Google authorize URL | Uses `includes/google_oauth.php` and `includes/app.php` |
| `pages/google_callback.php` | Google OAuth callback handler | Validates state, exchanges code for token, fetches Google profile, maps or creates local user through `resolve_google_user()`, logs user in, redirects to dashboard | Uses `includes/google_oauth.php`, `includes/app.php`, and shared auth helpers |

### `js/` directory

This folder contains page-specific browser-side behavior. None of these files replace server-side validation; they only improve UX.

| File | What it does | Why it exists | Connected pages |
|---|---|---|---|
| `js/login.js` | Adds minimal login form validation and password visibility support | Prevents obviously empty/short email submission and improves login UX | `pages/login.php`, `pages/signup.php` |
| `js/logout.js` | Runs a five-second countdown and redirects to `index.php` | Makes logout flow feel explicit and safe | `pages/logout.php` |
| `js/dashboard.js` | Handles mobile sidebar toggling and animates KPI counters | Makes dashboard stats feel live and improves mobile navigation | `pages/dashboard.php` |
| `js/products.js` | Implements search/filtering across the products table and powers the product detail modal | Avoids extra server round trips for common browsing actions | `pages/products.php` |
| `js/add_product.js` | Adds client-side validation, reset behavior, keyboard shortcut support, and mobile sidebar behavior | Catches invalid input early and improves admin product entry workflow | `pages/add_product.php` |
| `js/stock_in.js` | Adds stock-in form validation and reset behavior | Improves data-entry quality before server submit | `pages/stock_in.php` |
| `js/stock_out.js` | Adds stock-out validation, syncs available quantity into the `max` attribute, and prevents obvious over-removal in the UI | Reduces invalid submissions and surfaces live stock constraints to the admin | `pages/stock_out.php` |
| `js/low_stock.js` | Adds search and modal detail rendering for low-stock product cards | Makes alert review faster without reloading the page | `pages/low_stock.php` |
| `js/reports.js` | Filters the current-stock report by category and exports all visible report tables as a CSV-like file | Provides lightweight reporting/export without backend file generation | `pages/reports.php` |

### `css/` directory

These files are presentation-only. They do not contain business logic, but they matter because the UI structure is page-based.

| File | Purpose | Connected pages |
|---|---|---|
| `css/landing.css` | Styling for the public landing page (`index.php`) | `index.php` |
| `css/login.css` | Shared auth page styling for login and signup | `pages/login.php`, `pages/signup.php` |
| `css/logout.css` | Styling for logout confirmation screen | `pages/logout.php` |
| `css/dashboard.css` | Dashboard layout, cards, table, and quick-action styling | `pages/dashboard.php` |
| `css/products.css` | Product listing, search/filter area, table, and modal styling | `pages/products.php` |
| `css/add_product.css` | Shared styling for add/edit product forms | `pages/add_product.php`, `pages/edit_product.php` |
| `css/stock_in.css` | Form and history-table styling for stock-in workflow | `pages/stock_in.php` |
| `css/stock_out.css` | Form and history-table styling for stock-out workflow | `pages/stock_out.php` |
| `css/low_stock.css` | Alert cards, urgency styles, search, and modal styling | `pages/low_stock.php` |
| `css/reports.css` | Report cards, report sections, and table styling | `pages/reports.php` |
| `css/sidebar-shell.css` | Shared authenticated-app shell styling for sidebar and top header | Dashboard, products, add/edit product, stock-in, stock-out, low-stock, reports |

---

## 🗄️ Database Design

### Important note

This repository does **not** include a migration file or SQL schema dump. The schema below is therefore documented as the **application-required schema** reconstructed from:

- actual SQL queries in the PHP code
- expected inserts/updates/selects
- the legacy README schema

If the live database differs, the code paths in this repository still require at minimum the fields described below.

### Tables overview

The system revolves around four main tables:

- `users`
- `products`
- `stock_in`
- `stock_out`

### Relationship model

```text
users
  └── application session identity only

products
  ├── 1-to-many -> stock_in.product_id
  └── 1-to-many -> stock_out.product_id
```

Observations:

- `stock_in.product_id` references `products.id`
- `stock_out.product_id` references `products.id`
- `recorded_by` in stock tables is stored as plain text, not a foreign key to `users.id`
- current stock is stored directly in `products.quantity`, not derived from replaying all transactions

### `users` table

#### Application-required columns

| Column | Type | Meaning | Used by |
|---|---|---|---|
| `id` | `INT` primary key | Internal user identifier | session payload, user lookup helpers |
| `email` | `VARCHAR` unique | Login identifier for local auth and account matching for Google auth | login, signup, Google account resolution |
| `password` | `VARCHAR(255)` | Password hash for local users; placeholder hash for Google-only accounts | local authentication |
| `full_name` | `VARCHAR` | Display name shown in header and recorded-by text | header UI, Google sync, stock transaction display |
| `role` | `ENUM('admin','user')` or similar | Access control role | `require_admin()`, sidebar visibility |
| `auth_provider` | `VARCHAR` | Auth source such as `local` or `google` | Google account linking |
| `provider_user_id` | `VARCHAR` nullable | External identity ID, currently Google `sub` | Google account linking and lookup |
| `created_at` | timestamp, likely optional/defaulted | Audit timestamp if present | not currently used by code |

#### How the app uses `users`

- `login.php` calls `authenticate_user()` which queries by `email`
- `signup.php` checks duplicate `email` and inserts a new local user
- `google_callback.php` identifies or creates a matching user record using `email` and `provider_user_id`
- `login_user()` stores a subset of user fields inside `$_SESSION['auth_user']`

### `products` table

| Column | Type | Meaning | Used by |
|---|---|---|---|
| `id` | `VARCHAR(10)` style key like `P001` | Human-readable product identifier | all product, stock, and report pages |
| `name` | `VARCHAR` | Product display name | listings, search, reports, stock joins |
| `category` | `VARCHAR` or `ENUM` | Product grouping | forms, filters, reports |
| `price` | `DECIMAL(10,2)` | Unit price / unit value | product list, reports, total stock value |
| `quantity` | `INT` | Current on-hand stock | dashboard, products, low-stock, reports, stock validation |
| `reorder_level` | `INT` | Threshold at which an item is considered low stock | low-stock logic |
| `created_at` | timestamp, likely optional/defaulted | Audit timestamp if present | not used directly by code |
| `updated_at` | timestamp, likely optional/defaulted | Audit timestamp if present | not used directly by code |

#### How the app uses `products`

- serves as the source of truth for current stock quantity
- feeds dropdowns in stock-in and stock-out forms
- is updated directly by stock movement pages
- is queried for low-stock detection using `quantity <= reorder_level`
- is joined with stock history tables to show product names in activity/report views

### `stock_in` table

| Column | Type | Meaning | Used by |
|---|---|---|---|
| `id` | `INT` primary key | Stock-in record ID | history ordering |
| `product_id` | `VARCHAR` FK to `products.id` | Which product received stock | joins and history |
| `quantity` | `INT` | Amount added | dashboard/report/history totals |
| `supplier` | `VARCHAR` | Source/vendor name | stock-in history and reports |
| `date` | `DATE` | Business date of the stock-in event | dashboard, filters, totals |
| `notes` | `TEXT` nullable | Extra operator notes | stock-in history |
| `recorded_by` | `VARCHAR` | Human-readable user name captured at write time | activity tables and reports |
| `created_at` | timestamp, likely optional/defaulted | Insert timestamp if present | not explicitly used by code |

#### How the app uses `stock_in`

- `stock_in.php` inserts records here inside a transaction
- `dashboard.php` unions these rows with `stock_out`
- `reports.php` renders a stock-in report by joining to `products`
- `stock_in.php` calculates today/week inbound totals from this table

### `stock_out` table

| Column | Type | Meaning | Used by |
|---|---|---|---|
| `id` | `INT` primary key | Stock-out record ID | history ordering |
| `product_id` | `VARCHAR` FK to `products.id` | Which product lost stock | joins and history |
| `quantity` | `INT` | Amount removed | reports, dashboard activity, validation |
| `issued_to` | `VARCHAR` | Customer/recipient/destination | stock-out history and reports |
| `date` | `DATE` | Business date of the stock-out event | dashboard/report/history totals |
| `notes` | `TEXT` nullable | Reason or contextual notes | history |
| `recorded_by` | `VARCHAR` | Human-readable operator name | reports/activity display |
| `created_at` | timestamp, likely optional/defaulted | Insert timestamp if present | not explicitly used by code |

#### How the app uses `stock_out`

- `stock_out.php` inserts records here after stock is safely decremented
- `dashboard.php` unions these rows with `stock_in`
- `reports.php` renders a stock-out report by joining to `products`
- `stock_out.php` calculates today/week outbound totals from this table

### Recommended schema sketch

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    auth_provider VARCHAR(50) NOT NULL DEFAULT 'local',
    provider_user_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE stock_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    supplier VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    notes TEXT NULL,
    recorded_by VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_in_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
);

CREATE TABLE stock_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    issued_to VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    notes TEXT NULL,
    recorded_by VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_out_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
);
```

### Where database operations happen

#### Shared user/auth queries

- `includes/app.php`
  - `find_user_by_email()`
  - `find_user_by_google_id()`
  - `find_user_by_id()`
  - `link_google_identity_to_user()`
  - `create_google_user()`
  - `resolve_google_user()`
  - `authenticate_user()`
  - `next_product_id()`

#### Page-level product queries

- `pages/products.php`
  - reads all products
- `pages/add_product.php`
  - duplicate-name check
  - insert product
- `pages/edit_product.php`
  - select one product
  - duplicate-name check
  - update product
- `pages/delete_product.php`
  - delete product

#### Page-level stock queries

- `pages/stock_in.php`
  - validate product existence
  - insert stock-in row
  - increment product quantity
  - read totals/history
- `pages/stock_out.php`
  - validate available quantity
  - decrement product quantity safely
  - insert stock-out row
  - read totals/history

#### Reporting queries

- `pages/dashboard.php`
  - aggregated counts
  - recent activity union
- `pages/low_stock.php`
  - low-stock subsets
- `pages/reports.php`
  - stock value totals
  - current stock listing
  - stock-in and stock-out joined reports

---

## 🔐 Authentication and Session System

The application supports two authentication paths:

- local email/password auth
- Google OAuth sign-in

### Session bootstrap

Sessions are initialized in `includes/app.php` before any page logic runs.

What happens:

- `session_status()` is checked
- cookie params are set:
  - `httponly => true`
  - `secure => true` only when HTTPS is detected
  - `samesite => 'Lax'`
- `session_start()` is called

This means every page that includes `app.php` automatically participates in the same session/auth system.

### Local login flow

Step-by-step:

1. User opens `pages/login.php`.
2. `redirect_if_logged_in('dashboard.php')` prevents logged-in users from seeing the login form again.
3. User submits email and password.
4. `verify_csrf_token()` validates the hidden form token.
5. Inputs are trimmed and checked for emptiness.
6. `authenticate_user($conn, $identifier, $password)` runs:
   - normalizes email to lowercase
   - fetches user row by email
   - verifies the submitted password against the stored hash
   - includes a migration path for legacy plaintext passwords:
     - if direct string equality matches the stored password
     - the code re-hashes the password using `password_hash()`
     - updates the DB row
7. If authentication succeeds, `login_user($user)`:
   - calls `session_regenerate_id(true)`
   - stores `id`, `email`, `full_name`, `role`, and `auth_provider` in `$_SESSION['auth_user']`
8. A flash success message is stored.
9. Browser is redirected to `dashboard.php`.

### Signup flow

Step-by-step:

1. User opens `pages/signup.php`.
2. `redirect_if_logged_in('dashboard.php')` prevents duplicate signup for logged-in users.
3. User submits full name, email, password, and confirm password.
4. `verify_csrf_token()` validates the request.
5. Server-side validation checks:
   - all required fields present
   - valid email format
   - password length `>= 8`
   - password confirmation matches
6. The email is normalized to lowercase.
7. The app checks whether the email already exists.
8. If unique:
   - password is hashed with `password_hash()`
   - `auth_provider` is set to `local`
   - `role` is forced to `public_signup_role()`, which returns `user`
9. New row is inserted into `users`.
10. A success flash message is set.
11. Browser is redirected to `login.php`.

### Google OAuth flow

#### Start phase

1. User clicks "Continue With Google" on login or signup page.
2. Browser requests `pages/google_start.php?from=login` or `?from=signup`.
3. `google_oauth_is_configured()` checks that client ID, secret, and redirect URI exist.
4. App generates a random state token.
5. State token and origin are stored in session:
   - `$_SESSION['google_oauth_state']`
   - `$_SESSION['google_oauth_origin']`
6. Browser is redirected to the Google authorization URL.

#### Callback phase

1. Google redirects back to `pages/google_callback.php`.
2. The app reads the session-stored state and origin.
3. Session values are cleared immediately after retrieval.
4. The app validates:
   - OAuth config exists
   - request was not denied
   - returned `state` matches expected state
   - authorization code exists
5. App exchanges the code for an access token via `google_exchange_code_for_tokens()`.
6. App fetches Google profile data via `google_fetch_userinfo()`.
7. It extracts:
   - email
   - Google user ID (`sub`)
   - display name
8. `resolve_google_user()` runs a DB transaction:
   - if a user already exists with this Google ID, use it
   - else if a local account exists with the same email, link Google to that row
   - else create a new Google-backed user with default role `user`
9. `login_user()` creates the session.
10. Browser is redirected to `dashboard.php`.

### Session usage

These helpers read from `$_SESSION['auth_user']`:

- `current_user()`
- `current_user_name()`
- `current_username()`
- `current_user_email()`
- `current_user_role()`
- `is_logged_in()`
- `is_admin()`

These values drive:

- access control
- sidebar visibility
- header welcome text
- recorded-by names in stock transactions

### Logout flow

Step-by-step:

1. User opens `pages/logout.php`.
2. The page checks whether a user was logged in.
3. `logout_user()`:
   - clears `$_SESSION`
   - expires the session cookie if cookies are in use
   - calls `session_destroy()`
4. The page renders a confirmation screen.
5. `js/logout.js` counts down 5 seconds.
6. Browser is redirected to `../index.php`.

### Where authentication checks happen

- `require_login()`
  - defined in `includes/app.php`
  - used by dashboard, products, low_stock, reports, and indirectly by admin-only pages through `require_admin()`
- `require_admin()`
  - defined in `includes/app.php`
  - used by add/edit/delete product, stock-in, and stock-out pages
- `redirect_if_logged_in()`
  - defined in `includes/app.php`
  - used by login and signup pages

---

## ⚙️ Feature-by-Feature Internal Breakdown

### 1. Dashboard

#### Files involved

- `pages/dashboard.php`
- `css/dashboard.css`
- `css/sidebar-shell.css`
- `js/dashboard.js`
- shared UI/auth helpers in `includes/app.php`

#### What happens behind the scenes

When the page loads:

- user must be authenticated
- dashboard executes several aggregate queries:
  - total products
  - total quantity on hand
  - low-stock item count
  - total transaction count from `stock_in + stock_out`
- a UNION ALL query merges stock-in and stock-out activity into one recent-activity table

#### Database usage

Examples:

```sql
SELECT COUNT(*) AS total FROM products;
SELECT COALESCE(SUM(quantity), 0) AS total_stock FROM products;
SELECT COUNT(*) AS total FROM products WHERE quantity <= reorder_level;
SELECT (SELECT COUNT(*) FROM stock_in) + (SELECT COUNT(*) FROM stock_out) AS total_transactions;
```

#### Frontend behavior

- `js/dashboard.js` animates KPI numbers
- handles responsive sidebar toggle

### 2. Products

#### Files involved

- `pages/products.php`
- `pages/add_product.php`
- `pages/edit_product.php`
- `pages/delete_product.php`
- `css/products.css`
- `css/add_product.css`
- `css/sidebar-shell.css`
- `js/products.js`
- `js/add_product.js`

#### Product list internals

`pages/products.php`:

- requires login
- loads every row from `products`
- computes display state with `product_status(quantity, reorder_level)`
- embeds product data into `data-*` attributes on each table row
- exposes:
  - view button for all users
  - edit/delete only for admins

#### Add product internals

`pages/add_product.php`:

- admin-only
- validates:
  - name length
  - category in allowed list
  - price non-negative numeric
  - quantity integer `>= 0`
  - reorder level integer `>= 0`
- checks for duplicate product name with a case-insensitive query
- generates the next human-readable product ID through `next_product_id()`
- inserts into `products`

Important note about ID generation:

- `next_product_id()` scans existing product IDs in ascending order
- it looks for the first available gap in the `P001`, `P002`, `P003` sequence
- this means deleted IDs can be reused

#### Edit product internals

`pages/edit_product.php`:

- admin-only
- loads the product from `$_GET['id']`
- validates the same fields as add product
- checks for duplicate names excluding the current product ID
- updates the product row

#### Delete product internals

`pages/delete_product.php`:

- admin-only
- only accepts POST
- verifies CSRF token
- deletes product by `id`
- because stock tables are expected to reference `products.id`, cascaded deletes may remove related history if foreign keys are configured with `ON DELETE CASCADE`

#### Frontend behavior

`js/products.js`:

- filters table rows by search text, category, and status
- opens a modal populated from HTML `data-*` attributes

`js/add_product.js`:

- validates fields before submit
- supports reset behavior
- supports `Ctrl+Enter` submit shortcut

### 3. Stock In

#### Files involved

- `pages/stock_in.php`
- `css/stock_in.css`
- `css/sidebar-shell.css`
- `js/stock_in.js`

#### Internal flow

On GET:

- loads all products into a dropdown
- calculates today's inbound quantity
- calculates current week's inbound quantity
- loads full stock-in history joined with product names

On POST:

- admin-only and CSRF-protected
- validates:
  - product selected
  - quantity added `>= 1`
  - supplier length
  - date valid and not in future
- confirms selected product exists
- opens a DB transaction
- inserts into `stock_in`
- updates `products.quantity = quantity + added`
- commits or rolls back

#### Why the transaction matters

Without a transaction, the app could insert a stock movement row without updating the actual product quantity, or vice versa. The transaction keeps those two writes logically consistent.

### 4. Stock Out

#### Files involved

- `pages/stock_out.php`
- `css/stock_out.css`
- `css/sidebar-shell.css`
- `js/stock_out.js`

#### Internal flow

On GET:

- loads all products with current quantity
- calculates today's outbound quantity
- calculates current week's outbound quantity
- loads stock-out history joined with product names

On POST:

- admin-only and CSRF-protected
- validates:
  - product selected
  - quantity removed `>= 1`
  - issued-to/customer length
  - date valid and not in future
- pre-checks current available quantity
- opens transaction
- first attempts atomic decrement:

```sql
UPDATE products
SET quantity = quantity - ?
WHERE id = ? AND quantity >= ?
```

- if affected rows are not `1`, the app assumes stock changed concurrently or is insufficient
- only after successful decrement does it insert the `stock_out` record
- commits or rolls back

#### Why this implementation is stronger than stock-in

The decrement query prevents the quantity from going negative even if another request changes stock between validation and update. This is a basic concurrency guard.

### 5. Low Stock

#### Files involved

- `pages/low_stock.php`
- `css/low_stock.css`
- `css/sidebar-shell.css`
- `js/low_stock.js`

#### Internal logic

- accessible to any logged-in user
- calculates:
  - `criticalCount`: products with `quantity = 0`
  - `lowCount`: products with `quantity > 0 AND quantity <= reorder_level`
- loads all products where `quantity <= reorder_level`
- renders urgency card styles
- calculates percentage of stock relative to reorder level for display bars

This page is read-only unless the user is an admin, in which case a "Restock Now" link points to `stock_in.php`.

### 6. Reports

#### Files involved

- `pages/reports.php`
- `css/reports.css`
- `css/sidebar-shell.css`
- `js/reports.js`

#### Internal logic

This page composes three report datasets:

1. current stock report
2. stock-in report
3. stock-out report

It also calculates summary metrics:

- total stock value
- total products
- low-stock count
- total stock movement count

#### Query behavior

- current stock report reads from `products`
- stock-in report joins `stock_in` to `products`
- stock-out report joins `stock_out` to `products`
- unit cost/value is derived using the product's current `price`

#### Important implementation detail

The historical stock-in and stock-out reports compute total cost/value using the **current product price**, not the price at the time of the transaction. Because transaction rows do not store historical unit price snapshots, reports can drift when product prices change.

#### Frontend export behavior

`js/reports.js`:

- reads all rendered HTML tables
- serializes them into CSV-like text
- downloads a single file named `inventory_reports.csv`

This is a client-side export only. There is no server-side PDF/Excel export pipeline.

---

## 🧭 Navigation, Redirects, and Page Flow

### Main navigation map

```text
index.php
  ├── if guest -> links to signup/login
  └── if logged in -> links to dashboard

login.php / signup.php
  └── successful auth -> dashboard.php

dashboard.php
  ├── products.php
  ├── add_product.php (admin)
  ├── stock_in.php (admin)
  ├── stock_out.php (admin)
  ├── low_stock.php
  ├── reports.php
  └── logout.php
```

### Sidebar logic

The sidebar is rendered by `render_sidebar($activePage)` in `includes/app.php`.

Important behavior:

- it is server-rendered, not dynamically fetched
- it applies the active class based on current page file name
- it hides admin-only menu items from non-admin users

### Page gating summary

| Page | Guest | User | Admin |
|---|---|---|---|
| `index.php` | yes | yes | yes |
| `login.php` | yes | redirected | redirected |
| `signup.php` | yes | redirected | redirected |
| `dashboard.php` | no | yes | yes |
| `products.php` | no | yes | yes |
| `low_stock.php` | no | yes | yes |
| `reports.php` | no | yes | yes |
| `add_product.php` | no | no | yes |
| `edit_product.php` | no | no | yes |
| `delete_product.php` | no | no | yes |
| `stock_in.php` | no | no | yes |
| `stock_out.php` | no | no | yes |
| `logout.php` | yes | yes | yes |

---

## 🛡️ Security Implementation

### What is implemented well

#### 1. SQL injection prevention

Most write operations and sensitive reads use MySQLi prepared statements:

- login user lookup
- signup duplicate check and insert
- product add/edit/delete
- stock-in validation and insert/update
- stock-out validation and insert/update
- Google auth account lookup/link/create

This is the primary SQL injection protection mechanism in the app.

#### 2. Output escaping

`e()` wraps `htmlspecialchars()` and is used widely when outputting dynamic values into HTML. This reduces reflected/stored XSS risk for product names, user names, notes, and other displayed fields.

#### 3. CSRF protection

`includes/app.php` provides:

- `csrf_token()`
- `render_csrf_field()`
- `verify_csrf_token()`

These are applied on important forms including:

- login
- signup
- add product
- edit product
- stock in
- stock out
- delete product

#### 4. Session hardening

Sessions use:

- `HttpOnly` cookies
- `SameSite=Lax`
- `Secure` cookies when HTTPS is detected
- `session_regenerate_id(true)` on login

#### 5. Role-based access control

Admin-only workflows are protected both by:

- server-side `require_admin()`
- UI hiding in `render_sidebar()`

Server-side checks are what actually matter, and they are present.

### Honest weaknesses

#### 1. Real OAuth secrets are committed in the repository

`includes/google_oauth_config.local.php` contains a real client ID and client secret. That file should be treated as local-only secret config and removed from version control.

#### 2. No rate limiting or login throttling

There is no protection against repeated password attempts, so the system is vulnerable to brute-force attacks.

#### 3. No password reset or email verification

Accounts can be created and used immediately without ownership verification of the email address.

#### 4. No server-side session timeout or idle expiration

Sessions persist until logout or browser/session expiration behavior ends them. There is no inactivity timeout policy in code.

#### 5. Some read-only queries are raw SQL strings

Several dashboard/report/list queries use direct `$conn->query()` strings. These are not immediately dangerous because they do not interpolate user input, but the project uses mixed SQL styles instead of one consistent repository layer.

#### 6. Historical pricing is not preserved

Stock movement tables do not store unit price snapshots, so historical report values change if a product price is edited later.

#### 7. `recorded_by` is denormalized text

Stock movement tables store the display name string instead of `user_id`. This weakens auditability and can become inconsistent if a user's name changes.

#### 8. Product ID generation may race under concurrency

`next_product_id()` scans existing IDs and returns the next available one. Without a unique retry strategy or lock, concurrent product creation requests could calculate the same ID.

#### 9. Public signup is open

Although public signup is safely forced to the `user` role, the system currently allows unrestricted public account creation.

### Input validation approach

Validation happens in two layers:

#### Client side

- required field checks
- numeric checks
- date checks
- stock availability checks in stock-out UI

#### Server side

- authoritative validation in PHP
- all security decisions happen on the server
- client validation is only a UX enhancement

---

## 📍 Where Important Code Lives

### Core runtime

| Concern | File / function |
|---|---|
| Database connection | `includes/db_connection.php` |
| Session bootstrap | `includes/app.php` |
| HTML escaping | `includes/app.php` -> `e()` |
| Flash message system | `includes/app.php` -> `set_flash()`, `get_flash()`, `render_flash()` |
| CSRF generation/validation | `includes/app.php` -> `csrf_token()`, `render_csrf_field()`, `verify_csrf_token()` |
| Sidebar/top header rendering | `includes/app.php` -> `render_sidebar()`, `render_top_header()` |

### Authentication

| Concern | File / function |
|---|---|
| Local login page | `pages/login.php` |
| Signup page | `pages/signup.php` |
| Password authentication | `includes/app.php` -> `authenticate_user()` |
| Session creation | `includes/app.php` -> `login_user()` |
| Logout | `includes/app.php` -> `logout_user()` and `pages/logout.php` |
| Login-required guard | `includes/app.php` -> `require_login()` |
| Admin-required guard | `includes/app.php` -> `require_admin()` |
| Google OAuth config + HTTP calls | `includes/google_oauth.php` |
| Google OAuth start | `pages/google_start.php` |
| Google OAuth callback | `pages/google_callback.php` |
| Google/local account reconciliation | `includes/app.php` -> `resolve_google_user()` |

### Inventory domain logic

| Concern | File / function |
|---|---|
| Product status calculation | `includes/app.php` -> `product_status()` |
| Product ID generation | `includes/app.php` -> `next_product_id()` |
| Product listing | `pages/products.php` |
| Product creation | `pages/add_product.php` |
| Product editing | `pages/edit_product.php` |
| Product deletion | `pages/delete_product.php` |
| Stock-in transaction logic | `pages/stock_in.php` |
| Stock-out transaction logic | `pages/stock_out.php` |
| Low-stock detection | `pages/low_stock.php` and `includes/app.php` -> `product_status()` |
| Dashboard metrics/activity | `pages/dashboard.php` |
| Reports | `pages/reports.php` |

### Exact key locations

These are the most useful jump-in points for a new developer:

| Logic | Location |
|---|---|
| `require_login()` | `includes/app.php:76` |
| `require_admin()` | `includes/app.php:87` |
| `verify_csrf_token()` | `includes/app.php:139` |
| `product_status()` | `includes/app.php:174` |
| `next_product_id()` | `includes/app.php:187` |
| `resolve_google_user()` | `includes/app.php:323` |
| `authenticate_user()` | `includes/app.php:353` |
| `login_user()` | `includes/app.php:387` |
| `logout_user()` | `includes/app.php:399` |
| `render_sidebar()` | `includes/app.php:411` |
| Product insert | `pages/add_product.php:60` |
| Product update | `pages/edit_product.php:79` |
| Product delete | `pages/delete_product.php:22` |
| Signup insert | `pages/signup.php:43` |
| Stock-in insert | `pages/stock_in.php:65` |
| Stock-in quantity increment | `pages/stock_in.php:70` |
| Stock-out quantity decrement | `pages/stock_out.php:66` |
| Stock-out insert | `pages/stock_out.php:76` |
| Google OAuth config | `includes/google_oauth.php:5` |
| Google auth URL generation | `includes/google_oauth.php:46` |
| Google token exchange | `includes/google_oauth.php:128` |
| Google user info fetch | `includes/google_oauth.php:146` |
| OAuth state creation | `pages/google_start.php:15` |
| OAuth callback account resolution | `pages/google_callback.php:55` |

---

## 🚀 Setup and Local Development

### Prerequisites

- Apache or another PHP-capable local web server
- MySQL or MariaDB
- PHP with MySQLi enabled

### Database setup

Create a database named:

```sql
CREATE DATABASE inventory_system;
```

Then create the required tables using the schema in the [Database Design](#-database-design) section.

### Database config

Edit `includes/db_connection.php` if needed:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'inventory_system';
```

### Google OAuth config

Prefer this approach:

1. copy `includes/google_oauth_config.local.example.php`
2. create a local-only `includes/google_oauth_config.local.php`
3. keep real secrets out of Git

### Run locally

Place the project in your web root and open:

```text
http://localhost/Inventoy%20Management%20System%20(South%20C)/
```

### Recommended seed data

Create at least:

- one `admin` user
- one `user` account
- several sample products with different quantities and reorder levels

This allows every code path to be exercised quickly.

---

## 📈 Known Weaknesses and Improvement Opportunities

### Scalability improvements

1. Introduce a service/repository layer.
   - Move SQL and transaction logic out of page files into dedicated classes or modules.

2. Add pagination.
   - `products.php`, `stock_in.php`, `stock_out.php`, and `reports.php` currently load full datasets.

3. Add indexes.
   - Likely useful indexes:
   - `products(name)`
   - `products(category)`
   - `stock_in(product_id, date)`
   - `stock_out(product_id, date)`
   - `users(email)`
   - `users(auth_provider, provider_user_id)`

4. Replace scan-based product ID generation.
   - Use an auto-increment numeric surrogate plus a generated display code, or use a locked sequence table.

### Security improvements

1. Remove committed OAuth secrets and rotate them immediately.
2. Add password login throttling and account lockout rules.
3. Enforce HTTPS in production.
4. Add audit logging for admin actions.
5. Store `recorded_by_user_id` alongside or instead of `recorded_by`.
6. Add email verification and password reset flows.
7. Add CSP, secure headers, and stricter cookie policies in production.

### Code structure improvements

1. Split `includes/app.php` into focused modules.
   - auth
   - session
   - csrf
   - ui helpers
   - product helpers
   - Google identity helpers

2. Introduce a router/controller structure.
   - Keeps page files thinner and more maintainable.

3. Add a migration system.
   - Example options: Phinx, Doctrine Migrations, Laravel migrations, or a simple SQL migration runner.

4. Add automated tests.
   - unit tests for helpers
   - integration tests for form workflows
   - regression tests for auth and stock movement transactions

5. Normalize business constants.
   - categories should ideally come from DB or config, not hardcoded arrays.

### Data-model improvements

1. Store historical unit price on each stock movement row.
2. Add `user_id` foreign keys for auditability.
3. Add soft delete support for products if history must be preserved.
4. Add supplier/customer master tables if the domain grows.

### Future feature ideas

1. Purchase order and reorder workflow.
2. Barcode/QR support.
3. Stock adjustment page for corrections and audits.
4. Multi-location inventory support.
5. Role/permission matrix beyond just `admin` and `user`.
6. Dashboard charts and date-range analytics.
7. CSV/Excel import.
8. Server-side PDF/Excel report generation.
9. Activity logs and approval flows.

---

## ✅ Summary

This repository is a small but functional inventory platform with:

- solid server-rendered PHP foundations
- real session and CSRF protection
- role-gated admin operations
- transaction-backed stock movement updates
- lightweight but effective reporting

Its biggest technical debts are:

- lack of a formal schema/migration system
- secrets committed in the repo
- mixed responsibilities in page files
- limited auditing and historical pricing fidelity

For a small deployment, the current structure is workable. For a growing team or production rollout, the next step should be to formalize the architecture, secure configuration management, and add test coverage.
