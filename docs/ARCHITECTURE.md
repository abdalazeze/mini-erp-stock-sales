# Mini ERP — Architecture & Developer Documentation

Complete walkthrough of every layer: what was built, why each decision was made, and how the pieces connect.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Tech Stack](#2-tech-stack)
3. [Directory Structure](#3-directory-structure)
4. [Database Schema](#4-database-schema)
5. [Authentication & Session](#5-authentication--session)
6. [Role-Based Access Control](#6-role-based-access-control)
7. [Bilingual UI (EN / AR + RTL)](#7-bilingual-ui-en--ar--rtl)
8. [Controllers](#8-controllers)
9. [Models](#9-models)
10. [Libraries (Service Layer)](#10-libraries-service-layer)
11. [Invoice Flow — End to End](#11-invoice-flow--end-to-end)
12. [Concurrency Control](#12-concurrency-control)
13. [Low-Stock Report & CSV Export](#13-low-stock-report--csv-export)
14. [Frontend (No Build Step)](#14-frontend-no-build-step)
15. [Configuration Files](#15-configuration-files)
16. [Commit History](#16-commit-history)
17. [Known Limitations & Next Steps](#17-known-limitations--next-steps)

---

## 1. Project Overview

A mini Sales & Stock ERP built as a coding test for Smart Life. Features:

- Product catalogue with categories, search, pagination, and enable/disable
- Multi-warehouse stock management with manual adjustment
- Sales invoices: pick customer + warehouse, search products, edit quantities, live totals, percentage discount
- Concurrency-safe stock decrement (two parallel requests for the last unit — only one wins)
- Low-stock report with warehouse filter and CSV export
- Bilingual UI: English and Arabic with full RTL layout support
- Two user roles: `admin` (all warehouses) and `user_warehouse` (own warehouse only)

---

## 2. Tech Stack

| Layer       | Choice                                      | Reason                                                    |
|-------------|---------------------------------------------|-----------------------------------------------------------|
| Framework   | CodeIgniter 3.1.13                          | Specified by the brief                                    |
| PHP         | 7.4+ syntax (tested on WAMP PHP 8.1)        | Brief constraint; 8.x compatible in practice              |
| Database    | MySQL 8 / MariaDB 10.3+, InnoDB             | FK constraints + transactions required for concurrency    |
| CSS         | Bootstrap 5 (CDN) + Bootstrap RTL (CDN)     | No npm; RTL variant swapped per language session          |
| JS          | jQuery 3.7.1 (CDN)                          | AJAX product search + live invoice totals                 |
| Auth        | Custom session-based, `password_hash`       | No Ion Auth — too heavy for this scope                    |
| Composer    | Not used                                    | No external PHP packages needed                           |

---

## 3. Directory Structure

```
mini_erp_stock_sales/
├── application/
│   ├── config/
│   │   ├── autoload.php          # Libraries, helpers loaded globally
│   │   ├── config.php            # base_url, CSRF, session settings
│   │   ├── database.php          # DB credentials (git-ignored)
│   │   ├── database.php.example  # Template committed to repo
│   │   └── routes.php            # default_controller = auth, lang route
│   ├── controllers/
│   │   ├── Auth.php              # Login / logout
│   │   ├── Categories.php        # Categories CRUD (admin only)
│   │   ├── Customers.php         # Customers CRUD (admin only)
│   │   ├── Invoices.php          # Invoice create / list / view + product search AJAX
│   │   ├── Lang.php              # Language switcher (/lang/set/en|ar)
│   │   ├── Products.php          # Products list, add, edit, disable
│   │   ├── Reports.php           # Low-stock report + CSV export
│   │   ├── Stock.php             # Stock view + adjust + add entry
│   │   └── Warehouses.php        # Warehouses CRUD (admin only)
│   ├── core/
│   │   └── MY_Controller.php     # Base controller: auth check, lang load, scope helpers
│   ├── helpers/
│   │   └── app_helper.php        # current_lang() helper
│   ├── language/
│   │   ├── arabic/ui_lang.php    # All UI strings in Arabic
│   │   └── english/ui_lang.php   # All UI strings in English
│   ├── libraries/
│   │   ├── Auth_lib.php          # Login, logout, user(), is_admin()
│   │   └── Invoice_service.php   # Transaction: stock decrement + invoice insert
│   ├── models/
│   │   ├── Category_model.php
│   │   ├── Customer_model.php
│   │   ├── Invoice_model.php
│   │   ├── Product_model.php
│   │   ├── Stock_model.php
│   │   ├── User_model.php
│   │   └── Warehouse_model.php
│   └── views/
│       ├── auth/                 # login.php
│       ├── categories/           # index, create, edit
│       ├── customers/            # index, create, edit
│       ├── invoices/             # create, index, view
│       ├── layouts/              # header.php, footer.php
│       ├── products/             # index, create, edit
│       ├── reports/              # low_stock.php
│       ├── stock/                # index, adjust, add_entry
│       └── warehouses/           # index, create, edit
├── assets/
│   └── js/
│       └── invoice.js            # Invoice screen: product search, line management, totals
├── database/
│   ├── schema.sql                # All tables with FK constraints and indexes
│   └── seed.sql                  # Users, warehouses, categories, products, stock, customers
├── docs/
│   ├── Smart_Test.pdf            # Original brief
│   ├── SmartLife_Plan.md         # Build plan
│   ├── DECISIONS.md              # Done/not-done, key decisions, concurrency test output
│   └── ARCHITECTURE.md           # This file
├── tests/
│   └── concurrency_test.php      # curl_multi test: 2 simultaneous requests for last unit
├── .gitignore
├── .htaccess                     # mod_rewrite: removes index.php from URLs
├── README.md                     # Setup instructions + default credentials
└── index.php                     # CI3 front controller
```

---

## 4. Database Schema

All tables use `InnoDB` with `utf8mb4_unicode_ci`. Every foreign key column has an index.

### Tables

**`warehouses`** — `id`, `name`, `is_active`

**`users`** — `id`, `username`, `password` (bcrypt), `role` ENUM(`admin`, `user_warehouse`), `warehouse_id` FK → warehouses

**`categories`** — `id`, `name`, `is_active`

**`products`** — `id`, `category_id` FK, `code` UNIQUE, `name`, `price` DECIMAL(10,2), `alert_quantity` INT, `is_active`

**`stock`** — `id`, `product_id` FK, `warehouse_id` FK, `quantity` INT, UNIQUE(`product_id`, `warehouse_id`)
The UNIQUE constraint prevents duplicate rows per (product, warehouse) pair.

**`customers`** — `id`, `name`, `phone`, `email`

**`invoices`** — `id`, `invoice_no` UNIQUE, `customer_id` FK, `warehouse_id` FK, `user_id` FK, `subtotal`, `discount_percent`, `discount_amount`, `total`, `created_at`

**`invoice_lines`** — `id`, `invoice_id` FK, `product_id` FK, `qty`, `unit_price`, `line_total`

### Why `is_active` not soft-delete

Soft-delete (`deleted_at`) is the right choice when records are referenced by history and need to remain queryable. Here, disabled products still satisfy FK constraints on `invoice_lines`. `is_active` keeps queries simple — filter `WHERE is_active = 1` everywhere. Listed as a future improvement.

---

## 5. Authentication & Session

**File:** `application/libraries/Auth_lib.php`

CI3's built-in session library stores user data as a serialized array. No JWT, no Ion Auth.

```
attempt($username, $password)
  → User_model::get_by_username()
  → password_verify()
  → session->set_userdata(['user_id', 'username', 'role', 'warehouse_id'])

check()   → bool: is user_id in session?
user()    → stdClass from session userdata
logout()  → sess_destroy()
is_admin() → role === 'admin'
```

CSRF protection is enabled in `config.php` (`csrf_protection = TRUE`). The token is also injected as a `<meta>` tag in the layout header so that jQuery AJAX calls can read it without a page refresh.

---

## 6. Role-Based Access Control

Two roles enforced at **two independent layers**:

### Layer 1 — Controller guards (`MY_Controller.php`)

```php
// Blocks non-admins from the action entirely
protected function _admin_only()

// Returns the warehouse_id the current user is allowed to act on.
// For user_warehouse: always their own, ignoring any request param.
// For admin: reads from GET or POST.
protected function _scoped_warehouse_id($method = 'get')

// Hard-stops a user_warehouse from accessing another warehouse's resource.
protected function _warehouse_guard($warehouse_id)
```

`Admin_Controller` extends `MY_Controller` and rejects non-admins in its constructor. Categories, Customers, Warehouses all extend it.

### Layer 2 — Query scoping

Every stock and invoice query that accepts a `$warehouse_id` parameter injects `WHERE warehouse_id = ?` when the value is set. The value always comes from `_scoped_warehouse_id()`, never directly from user input.

### Invoice pricing enforcement

- `user_warehouse`: the price input in the form is `readonly`. On the server, the posted unit_price is **ignored** and replaced with `product.price` from the database.
- `admin`: the posted price is accepted but validated (must be positive, max 2 decimal places).

This is enforced in `Invoices::save()`, not in JavaScript.

---

## 7. Bilingual UI (EN / AR + RTL)

### Language files

```
application/language/english/ui_lang.php   → $lang['key'] = 'English text';
application/language/arabic/ui_lang.php    → $lang['key'] = 'النص العربي';
```

Loaded in `MY_Controller::_load_language()` on every request based on the session value.

### Switcher

`GET /lang/set/ar` or `/lang/set/en` → `Lang::set()` stores the choice in session, redirects back via HTTP_REFERER.

### Default language

Changed to Arabic (`'ar'`) in `app_helper.php`. New sessions start in Arabic.

### RTL layout

`application/views/layouts/header.php` reads `current_lang()`:

```php
<html dir="<?= current_lang() === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= current_lang() ?>">
```

Bootstrap RTL stylesheet is swapped conditionally:
```php
// Arabic: bootstrap.rtl.min.css
// English: bootstrap.min.css
```

All view strings go through `lang('key')` — no hardcoded English anywhere in views.

---

## 8. Controllers

### `Auth`
- `GET  /auth/login`  — show form
- `POST /auth/login`  — validate credentials, set session, redirect to `/`
- `GET  /auth/logout` — destroy session, redirect to login

### `Categories`, `Warehouses`, `Customers`
All extend `Admin_Controller`. Standard CRUD: `index`, `create`, `edit`, `toggle` (enable/disable for categories/warehouses).

### `Products`
Extends `Admin_Controller` for write actions. `index` and `search_product` are accessible to all logged-in users.
- Pagination: manual GET-param based (`?page=N`). CI3's Pagination library was skipped because it conflicts with URI segment routing when search filters are also GET params.
- `_apply_filters()`: private method shared by `get_list()` and `count_list()` to apply search + category filter without duplication.

### `Stock`
- `index`: warehouse-scoped list. Admin sees a warehouse filter dropdown.
- `adjust`: admin-only, applies a signed integer delta to the current quantity.
- `add_entry`: admin shortcut to create a stock row by picking product + warehouse, then redirects to `adjust`.

### `Invoices`
- `create`: renders the invoice form. `user_warehouse` gets their warehouse pre-selected (disabled input + hidden field).
- `save`: validates lines, enforces pricing rules, calls `Invoice_service::create()`.
- `search_product`: AJAX endpoint, returns JSON array for the product search dropdown.
- `index` / `view`: list and detail pages, warehouse-scoped.

### `Reports`
- `low_stock`: filters `WHERE quantity <= alert_quantity`, supports warehouse filter + product search.
- `low_stock_csv`: same query, streams as CSV with UTF-8 BOM (needed for Excel to display Arabic correctly). Flushes all output buffers before streaming, exits after.

### `Lang`
- `set($lang)`: validates `$lang` is `en` or `ar`, stores in session, redirects.

---

## 9. Models

Each model follows CI3 conventions: thin, no business logic, returns `result()` (array of stdClass) or `row()`.

### `Product_model`
- `get_list()` / `count_list()`: both call `_apply_filters()` which applies search and category_id filter to the active query.
- `search($q)`: LIKE on `name` and `code`, active products only. Used by the invoice AJAX endpoint.
- `code_exists($code, $exclude_id)`: uniqueness check that excludes the current record on edit.

### `Stock_model`
- `get_list($warehouse_id)`: JOINs products + categories + warehouses, selects `s.product_id` and `w.id AS warehouse_id` for the adjust link.
- `get_low_stock($warehouse_id, $search)`: `WHERE s.quantity <= p.alert_quantity` using `where($key, $val, false)` to pass an unquoted column reference instead of a value.
- `adjust($product_id, $warehouse_id, $delta)`: upsert — creates row if it doesn't exist, floors at 0.

### `Invoice_model`
- `get_list()` / `count_list()` / `get()`: all accept `$warehouse_id` for query scoping.
- `get_lines($invoice_id)`: joins products to return code + name alongside qty and price.

---

## 10. Libraries (Service Layer)

### `Auth_lib`

Thin wrapper around CI3 sessions. See §5.

### `Invoice_service`

Contains the only real business logic in the app: creating an invoice inside a database transaction.

```
create($data)
  trans_begin()
  foreach line:
    atomic UPDATE stock WHERE quantity >= qty
    if affected_rows == 0 → trans_rollback(), return error
  compute subtotal, discount_amount, total
  INSERT invoices (temp invoice_no via uniqid())
  get insert_id()
  compute final invoice_no: INV-YYYY-NNNNNN
  UPDATE invoices SET invoice_no = ...
  INSERT invoice_lines
  trans_commit()
  return ['invoice_id' => ..., 'invoice_no' => ...]
```

`trans_begin / trans_commit / trans_rollback` (not `trans_start/complete`) is used deliberately because we need to inspect `affected_rows()` between queries before deciding whether to commit.

---

## 11. Invoice Flow — End to End

1. **User opens `/invoices/create`**
   - Admin: warehouse dropdown lists all active warehouses
   - `user_warehouse`: sees their warehouse name as read-only text; a hidden input carries the value

2. **Product search (AJAX)**
   - Keyup event on the search box (debounced 300ms) fires `GET /invoices/search_product?q=...`
   - Server calls `Product_model::search()`, returns JSON
   - JS renders a dropdown; clicking a result calls `addLine()`

3. **Line management (JS — `assets/js/invoice.js`)**
   - `addLine()`: if the product is already in the table, increments qty instead of adding a duplicate row
   - Qty/price changes update only the affected row (no full re-render, so the user doesn't lose focus)
   - Remove button re-renders the full table to fix sequential `lines[N]` input name indices
   - Subtotal, discount amount, and total recalculate on every change

4. **Form submit**
   - JS guards: prevents submit if no lines
   - Server (`Invoices::save()`): re-validates everything — warehouse scope, line validity, pricing rules
   - Calls `Invoice_service::create()`
   - On success: redirect to `/invoices/view/{id}`
   - On stock error: flash message, redirect back to create

5. **`invoice_no` generation**
   - Cannot pre-compute the auto-increment ID
   - INSERT with `uniqid('INV', true)` as a temporary unique value (satisfies the UNIQUE constraint)
   - After `insert_id()`: UPDATE to final format `INV-YYYY-NNNNNN`
   - Both happen inside the same transaction

---

## 12. Concurrency Control

**Problem:** Two requests both read `quantity = 1`, both pass the stock check, both decrement — one ends at `quantity = -1`.

**Solution — atomic conditional UPDATE:**

```sql
UPDATE stock
SET    quantity = quantity - ?
WHERE  product_id = ? AND warehouse_id = ? AND quantity >= ?;
```

Followed immediately by `$this->db->affected_rows()`. If 0, rollback and return an error naming the product.

**Why this works:** The `WHERE quantity >= ?` predicate collapses the "read, check, write" into a single atomic operation. The database engine serializes concurrent UPDATEs on the same row — one wins (affected_rows = 1), one loses (affected_rows = 0). No explicit `SELECT … FOR UPDATE` lock needed.

**Test script:** `tests/concurrency_test.php`

Uses `curl_multi` to fire two simultaneous POST requests, each trying to buy the last unit of a product. Both sessions are pre-logged-in with separate CSRF tokens.

**Expected output:**
```
Logging in as admin (two sessions)...
Firing 2 simultaneous requests for product #4...
Request 1 landed at: http://erp.local/invoices/view/3
Request 2 landed at: http://erp.local/invoices/create
Result: 1 succeeded, 1 rejected.
PASS — concurrency control working correctly.
```

---

## 13. Low-Stock Report & CSV Export

**Report:** `GET /reports/low_stock`

Query: `WHERE s.quantity <= p.alert_quantity`. The shortage column is computed in SQL as `(p.alert_quantity - s.quantity)`. Supports warehouse filter (admin only) and product name/code search.

**CSV export:** `GET /reports/low_stock_csv`

Same query. Before streaming:
1. `while (ob_get_level()) ob_end_clean()` — flushes CI3's output buffer so headers can be set
2. Sends `Content-Type: text/csv; charset=utf-8` and `Content-Disposition: attachment`
3. Writes UTF-8 BOM (`\xEF\xBB\xBF`) — Excel requires this to open Arabic files correctly
4. Streams rows with `fputcsv()` to `php://output`
5. `exit()` to prevent CI3's shutdown sequence from appending anything

---

## 14. Frontend (No Build Step)

No npm, no Webpack, no Vite. All assets are either CDN or hand-written.

**CDN libraries loaded in `layouts/header.php`:**
- Bootstrap 5.3 (`bootstrap.min.css` or `bootstrap.rtl.min.css` depending on language)
- jQuery 3.7.1
- Bootstrap Bundle JS (includes Popper)

**`assets/js/invoice.js`**

Reads configuration from a `INVOICE_CFG` object injected by the `invoices/create` view:
```js
var INVOICE_CFG = {
    searchUrl: '<?= base_url('invoices/search_product') ?>',
    isAdmin:   <?= $is_admin ? 'true' : 'false' ?>,
    noLines:   '<?= lang('invoice_no_lines') ?>',
    noResults: '<?= lang('msg_no_records') ?>'
};
```

CSRF token is read from the `<meta name="csrf_token">` tag in the layout and injected into every AJAX POST.

---

## 15. Configuration Files

### `application/config/config.php`
- `$config['base_url']` — must match your local domain (e.g., `http://erp.local/`)
- `$config['index_page'] = ''` — clean URLs (no `index.php` in the URL)
- `$config['csrf_protection'] = TRUE`
- `$config['csrf_token_name'] = 'csrf_token'`
- `$config['log_threshold'] = 0` — set to `4` temporarily to debug CI3 errors

### `application/config/autoload.php`
```php
$autoload['libraries'] = ['database', 'session', 'form_validation'];
$autoload['helper']    = ['url', 'form', 'html', 'app', 'language'];
```
The `language` helper provides the `lang()` function used in all views.

### `application/config/database.php`
Git-ignored. Copy from `database.php.example` and fill in credentials. WAMP defaults: `hostname = localhost`, `username = root`, `password = ''`.

### `.htaccess`
```apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```
Requires `mod_rewrite` to be enabled in Apache. In WAMP: tray icon → Apache → Apache Modules → rewrite_module.

---

## 16. Commit History

| # | Commit | What it contains |
|---|--------|-----------------|
| 1 | `chore: init CI3 skeleton` | CI3 files, base config, .gitignore, .htaccess |
| 2 | `feat: db schema + seed SQL` | schema.sql (all tables + FKs), seed.sql (users, warehouses, products, stock, customers) |
| 3 | `feat: auth` | Auth controller, Auth_lib, MY_Controller, login/logout views |
| 4 | `feat: bilingual UI scaffolding` | Language files (EN + AR), Lang controller, RTL layout toggle, header/footer partials |
| 5 | `feat: categories CRUD` | Categories controller + model + views (list, create, edit, toggle) |
| 6 | `feat: products list` | Product list with search, category filter, pagination |
| 7 | `feat: products add/edit/disable` | Product create/edit forms, code uniqueness check, disable/enable |
| 8 | `feat: warehouses CRUD` | Warehouses controller + model + views |
| 9 | `feat: stock view + adjust` | Stock list (warehouse-scoped), adjust form, add_entry shortcut |
| 10 | `feat: customers CRUD` | Customers controller + model + views |
| 11 | `feat: invoice screen UI` | Invoice create view, product search AJAX, invoice.js, admin-editable price |
| 12 | `feat: invoice save (concurrency-safe)` | Invoice_service with transaction + atomic stock UPDATE |
| 13 | `feat: invoice list + details` | Invoice index (paginated, scoped) + view page |
| 14 | `feat: low-stock report + CSV` | Reports controller, low_stock view, CSV export with UTF-8 BOM |
| 15 | `feat: warehouse-scoped permissions` | _scoped_warehouse_id(), _warehouse_guard(), view fixes |
| 16 | `docs: README + DECISIONS` | README.md, DECISIONS.md |
| 17 | `fix: autoload language helper` | Added `language` to autoload helpers so `lang()` works in views |
| 18 | `fix: product_id in stock select` | Added `s.product_id` to Stock_model::get_list() |
| 19 | `feat: default language Arabic` | Changed default lang from `en` to `ar` in app_helper.php |
| 20 | `fix: update concurrency test BASE_URL` | Changed localhost:8000 to erp.local in concurrency_test.php |
| 21 | `docs: add full architecture documentation` | docs/ARCHITECTURE.md — full developer walkthrough |
| 22 | `chore: move SmartLife_Plan.md into docs/` | Organised planning docs under docs/ |
| 23 | `chore: move DECISIONS.md into docs/` | Organised decision docs under docs/ |
| 24 | `docs: add docs/ reference table to README` | Reviewer-facing links to all docs |
| 25 | `docs: update directory structure` | Kept ARCHITECTURE.md in sync after file moves |

---

## 17. Known Limitations & Next Steps

### Limitations
- No VAT/tax — prices are stored and displayed as-is
- Integer quantity only — no support for kg, metres, etc.
- No invoice cancel/return flow
- No stock transfer between warehouses
- No audit log for stock changes
- No password reset
- No user management UI — users must be added via SQL

### What would come next in a real project
- `stock_movements` audit table — every decrement and manual adjustment with user + timestamp
- Stock transfer: two-row UPDATE inside a transaction (same atomic pattern)
- VAT per product or per invoice with a separate line on the invoice total
- Invoice cancel: status column + stock restore inside a transaction
- Soft-delete (`deleted_at`) replacing `is_active` for full historical integrity
- Background queue for large CSV exports
- Per-line discount on top of the invoice-level discount
- PHPUnit suite covering the service layer (Invoice_service, pricing rules, scoping)