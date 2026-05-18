# QR-Based Inventory Management System
### Presentation Guide

---

## What is this system?

A web-based inventory management system built with **Laravel 12** (PHP framework). It lets a business track their products, stock levels, and movements using **QR codes**. Every product gets a unique QR code. Scanning it logs the action and takes you straight to that product's page.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 + Laravel 12 |
| Frontend | Blade templates + Tailwind CSS + Alpine.js |
| Database | MySQL (via XAMPP) |
| Charts | Chart.js |
| QR Codes | endroid/qr-code + simplesoftwareio/simple-qrcode |
| Auth | Laravel Breeze |

---

## How to Run It

1. Start **XAMPP** — make sure **Apache** and **MySQL** are both green
2. Open a terminal in the project folder
3. Run: `php artisan serve`
4. Open your browser at: **http://127.0.0.1:8000**
5. Login with:
   - **Email:** admin@inventory.com
   - **Password:** password

---

## User Roles

There are two roles in the system. No one can self-register — accounts are created by the superadmin only.

### Superadmin
- Full access to everything
- **Only role that can create new admin accounts**
- Has a "Manage Admins" section in the sidebar
- Login: `admin@inventory.com` / `password`

### Admin
- Can view and manage all inventory (products, stock, categories, locations, reports)
- Cannot create new accounts
- Created by the superadmin from the Manage Admins page

**How it works technically:**
- The `users` table has a `role` column (`superadmin` or `admin`)
- A `SuperAdminMiddleware` checks the role before allowing access to admin management routes
- The public `/register` route is disabled — the only way to create accounts is through `/admins/create` (superadmin only)

---

## Core Features

### 1. Dashboard
The home screen after login. Shows:
- **6 stat cards** — Total Products, Active, Low Stock, Out of Stock, Categories, Inventory Value (in ₹)
- **Recent Transactions** — last 10 stock movements
- **Low Stock Alerts** — products that have fallen below their reorder point
- **Recent QR Scans** — log of who scanned what and when

### 2. Products
The main product catalogue. Each product has:
- Name, SKU, Barcode, Description
- Category and Location
- Selling Price (₹) and Cost Price (₹)
- Current Quantity and Reorder Point (minimum quantity before alert)
- Status: Active / Inactive / Discontinued
- A unique QR Code (auto-generated on creation)

**From the product detail page you can:**
- Do a quick Stock In, Stock Out, or manual Adjustment
- Download the QR code as SVG or PNG
- Print a QR label
- See the full transaction history for that product

### 3. QR Codes
Every product gets two QR code files generated automatically:
- **SVG** — used for display in the browser
- **PNG** — high resolution (600px) for printing

The QR code encodes a URL like: `http://127.0.0.1:8000/qr/scan/ELEC-001`

When someone scans it with a phone:
1. The system logs the scan (product, user, IP, timestamp)
2. Redirects to the product detail page

The **QR Scanner page** (`/qr/scanner`) uses the device camera to scan codes directly in the browser.

### 4. Inventory Transactions
Every stock change is recorded. There are 4 types:
- **Stock In** — adding stock (deliveries, returns)
- **Stock Out** — removing stock (sales, usage)
- **Adjustment** — setting an exact quantity (after a physical count)
- **Transfer** — moving stock between locations

Each transaction records: quantity before, quantity after, who did it, when, and a reference note.

### 5. Categories
Organise products into colour-coded groups (e.g. Electronics, Office Supplies, Tools). Used for filtering and reporting.

### 6. Locations
Physical storage locations with a building, floor, and aisle. Each product is assigned to one location (e.g. "Warehouse A - Shelf 1").

### 7. Reports & Analytics
- **4 KPI cards** — Total SKUs, Stock Value at Cost (₹), Retail Value (₹), Transactions this month
- **Bar chart** — transaction volume over the last 30 days (Stock In / Out / Adjustment / Transfer)
- **Stock Value by Category** table
- **Top 10 Most Moved Products** in the last 30 days
- **Low Stock** and **Out of Stock** tables
- Export to CSV (Products and Transactions)

---

## Currency

All prices are displayed in **Indian Rupees (₹)**. This applies to:
- Product selling price and cost price
- Inventory Value on the dashboard
- Stock Value and Retail Value on reports
- Stock value per category

---

## Database Tables

| Table | What it stores |
|---|---|
| `users` | Login accounts with roles (superadmin / admin) |
| `products` | Product catalogue with prices, stock, QR code path |
| `categories` | Product categories with colour codes |
| `locations` | Physical storage locations |
| `inventory_transactions` | Every stock movement ever made |
| `qr_scans` | Log of every QR code scan |
| `cache` | Laravel session/cache data |
| `jobs` | Background job queue |

---

## File Structure (Key Files)

```
app/
  Http/
    Controllers/
      DashboardController.php     — dashboard stats and chart data
      ProductController.php       — full product CRUD + QR download
      InventoryController.php     — stock in/out/adjust/transfer
      QrController.php            — QR scan handling + scanner page
      ReportController.php        — analytics and CSV exports
      CategoryController.php      — category management
      LocationController.php      — location management
      AdminController.php         — superadmin: create/list/delete admins
    Middleware/
      SuperAdminMiddleware.php    — blocks non-superadmin from admin routes
  Models/
    Product.php                   — product model with stock status helpers
    User.php                      — user model with isSuperAdmin() helper
    InventoryTransaction.php      — transaction model
    QrScan.php                    — scan log model
  Services/
    QrCodeService.php             — generates SVG + PNG QR code files

resources/views/
  dashboard.blade.php             — main dashboard
  products/                       — product list, detail, create, edit
  inventory/                      — transaction log
  reports/                        — analytics page
  admin/                          — manage admins (superadmin only)
  qr/                             — scanner, quick actions, bulk print
  layouts/app.blade.php           — main layout with sidebar nav

routes/
  web.php                         — all application routes
  auth.php                        — login/logout (register disabled)

database/
  migrations/                     — database table definitions
  seeders/DatabaseSeeder.php      — creates superadmin + sample data
```

---

## Sample Data (loaded by seeder)

**Categories:** Electronics, Office Supplies, Furniture, Tools, Packaging

**Locations:** Warehouse A Shelf 1 & 2, Warehouse B Rack 1, Office Storage, Reception

**Products:**
| Product | SKU | Price | Stock |
|---|---|---|---|
| USB-C Hub 7-Port | ELEC-001 | ₹49.99 | 45 |
| Wireless Keyboard | ELEC-002 | ₹79.99 | 8 ⚠️ Low |
| 27" Monitor | ELEC-003 | ₹299.99 | 12 |
| A4 Paper Ream | OFF-001 | ₹8.99 | 200 |
| Ballpoint Pens Box | OFF-002 | ₹5.99 | 3 ⚠️ Low |
| Cordless Drill | TOOL-001 | ₹129.99 | 0 ❌ Out |
| Safety Gloves (L) | TOOL-002 | ₹12.99 | 60 |

---

## Key Things to Mention in Your Presentation

1. **QR codes are the core** — every product has one, scanning it logs the action automatically
2. **Role-based access** — superadmin controls who gets in, admins can't create accounts
3. **Full audit trail** — every stock change is recorded with who did it and when
4. **Real-time alerts** — low stock and out-of-stock are flagged on the dashboard
5. **Currency is ₹ Rupees** throughout the entire system
6. **No self-registration** — the system is closed, only superadmin can add users
7. **Export to CSV** — reports can be downloaded for external use

---

*Good luck with your presentation!*


---

## Which Database is Used and Why

The system uses **MySQL** — specifically the MySQL server that comes bundled with **XAMPP**.

| Setting | Value |
|---|---|
| Database Engine | MySQL |
| Host | 127.0.0.1 |
| Port | 3306 |
| Database Name | qr_inventory |
| Username | root |
| Password | (none — XAMPP default) |

This is configured in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_inventory
DB_USERNAME=root
DB_PASSWORD=
```

Laravel talks to MySQL through its **Eloquent ORM** — you never write raw SQL. Instead you write PHP like `Product::create([...])` and Laravel converts it to the correct SQL query automatically.

You can view all the data visually at **http://localhost/phpmyadmin** → select `qr_inventory`.

---

## How the Database Tables are Structured

Laravel uses **Migrations** — PHP files that define the exact structure of each table. Running `php artisan migrate` executes them in order and creates all the tables.

### `users` table
Stores login accounts.
```
id            — auto-increment primary key
name          — full name
email         — unique, used to log in
password      — bcrypt hashed (never stored as plain text)
role          — 'superadmin' or 'admin'
remember_token — for "remember me" sessions
created_at / updated_at
```

### `products` table
The main product catalogue.
```
id            — primary key
name          — product name
sku           — unique stock-keeping unit code (e.g. ELEC-001)
barcode       — optional, unique
description   — optional text
category_id   — foreign key → categories table
location_id   — foreign key → locations table
price         — decimal(10,2) — selling price in ₹
cost          — decimal(10,2) — purchase/cost price in ₹
quantity      — current stock count (integer)
min_quantity  — reorder threshold — alert triggers when quantity ≤ this
unit          — pcs, kg, litre, box, etc.
image         — file path to uploaded product image (nullable)
qr_code       — file path to generated QR SVG (e.g. qrcodes/qr-ELEC-001.svg)
status        — enum: active / inactive / discontinued
deleted_at    — soft delete (record is hidden, not permanently removed)
created_at / updated_at
```

> **Soft Deletes** — when you delete a product, it is NOT removed from the database. The `deleted_at` timestamp is set instead. This preserves the transaction history. The product just disappears from all views.

### `inventory_transactions` table
Every single stock movement ever made. Never edited or deleted.
```
id               — primary key
product_id       — which product (foreign key)
user_id          — who did it (foreign key → users)
type             — enum: in / out / adjustment / transfer
quantity         — positive for in, negative for out
quantity_before  — stock level before this action
quantity_after   — stock level after this action
from_location_id — source location (for transfers)
to_location_id   — destination location (for transfers/stock-in)
reference        — optional PO number, invoice number, etc.
notes            — optional free text reason
created_at / updated_at
```

### `qr_scans` table
Logs every time a QR code is scanned.
```
id          — primary key
product_id  — which product was scanned
user_id     — who scanned it (null if not logged in)
action      — 'view', 'stock_in', or 'stock_out'
ip_address  — scanner's IP address
user_agent  — browser/device info
created_at / updated_at
```

### `categories` table
```
id, name, color (hex code e.g. #6366f1), description, timestamps
```

### `locations` table
```
id, name, code (e.g. WH-A1), building, floor, aisle, timestamps
```

---

## How Validation Works

Validation is the process of checking that data submitted through a form is correct before saving it to the database. Laravel rejects bad data and sends the user back with error messages automatically.

### How it works step by step

1. User fills in a form and clicks Submit
2. The browser sends the data to the server (HTTP POST request)
3. The controller calls `$request->validate([...rules...])`
4. Laravel checks every field against its rules
5. **If any rule fails** → Laravel redirects back to the form, the old input is kept, and error messages appear in red under each field
6. **If all rules pass** → execution continues and the data is saved

### Login Validation (`LoginRequest.php`)

```
email    → required, must be a valid email format
password → required
```

**Extra security — Rate Limiting:**
If someone enters the wrong password **5 times in a row**, the account is **locked for 60 seconds**. This prevents brute-force attacks (trying thousands of passwords). The lock is per email + IP address combination.

### Create Product Validation (`ProductController.php`)

| Field | Rules | What it means |
|---|---|---|
| name | required, string, max 255 | Cannot be empty, max 255 characters |
| sku | required, unique:products | Cannot be empty, must not already exist in the products table |
| barcode | nullable, unique:products | Optional, but if provided must be unique |
| price | required, numeric, min:0 | Must be a number, cannot be negative |
| cost | required, numeric, min:0 | Must be a number, cannot be negative |
| quantity | required, integer, min:0 | Must be a whole number, cannot be negative |
| min_quantity | required, integer, min:0 | Must be a whole number, cannot be negative |
| status | required, in:active,inactive,discontinued | Must be one of these three exact values |
| category_id | nullable, exists:categories,id | If provided, must match a real category in the database |
| location_id | nullable, exists:locations,id | If provided, must match a real location in the database |
| image | nullable, image, max:2048 | Optional, must be an image file, max 2MB |

**Edit Product** has the same rules, except the `unique` check for SKU and barcode **ignores the current product** (so you can save without changing the SKU).

### Stock Out Validation (`InventoryController.php`)

```
product_id → required, must exist in products table
quantity   → required, integer, minimum 1
```

**Plus a business logic check** — after validation passes, the code checks:
```php
if ($product->quantity < $validated['quantity']) {
    return back()->withErrors(['quantity' => 'Insufficient stock.']);
}
```
This prevents stock from going below zero.

### Create Admin Validation (`AdminController.php`)

```
name     → required, string, max 255
email    → required, valid email, must be lowercase, unique in users table
password → required, must match password_confirmation field,
           must meet Laravel's default password strength rules
           (minimum 8 characters)
```

### Transfer Validation (`InventoryController.php`)

```
product_id       → required, exists in products
quantity         → required, integer, min 1
from_location_id → required, exists in locations
to_location_id   → required, exists in locations, different:from_location_id
```
The `different` rule ensures you cannot transfer a product to the same location it's already in.

---

## How QR Codes are Generated

The system uses a PHP library called **`endroid/qr-code`** (installed via Composer). Here is the exact process from start to finish:

### Step 1 — Build the URL
When a new product is created, the system builds a URL using the product's SKU:
```
http://127.0.0.1:8000/qr/scan/ELEC-001
```
This URL is what gets encoded inside the QR code. When someone scans it, their phone opens this URL.

### Step 2 — Generate SVG (for display in the browser)
```php
Builder::create()
    ->writer(new SvgWriter())       // output format: SVG vector image
    ->data($url)                    // the URL to encode
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(ErrorCorrectionLevel::High)  // can recover if 30% of QR is damaged
    ->size(300)                     // 300px
    ->margin(10)                    // white border around the QR
    ->build();
```
The SVG file is saved to: `storage/app/public/qrcodes/qr-ELEC-001.svg`

### Step 3 — Generate PNG (for printing/downloading)
Same process but with `PngWriter()` and a larger size (600px) for print quality.
Saved to: `storage/app/public/qrcodes/qr-ELEC-001.png`

### Step 4 — Save the path to the database
The relative path `qrcodes/qr-ELEC-001.svg` is saved in the `products.qr_code` column.

### Step 5 — Display in the browser
The `Product` model has an accessor that converts the stored path to a full public URL:
```php
public function getQrCodeUrlAttribute(): string
{
    return asset('storage/' . $this->qr_code);
    // → http://127.0.0.1:8000/storage/qrcodes/qr-ELEC-001.svg
}
```
The blade view uses `$product->qr_code_url` as the `src` of an `<img>` tag.

### Step 6 — What happens when scanned
When a phone scans the QR code, it opens the URL. The server:
1. Finds the product by SKU
2. Creates a record in the `qr_scans` table (product, user, IP, timestamp)
3. Redirects to the product detail page

### Error Correction Level — High
The QR codes use **High error correction**. This means up to **30% of the QR code can be damaged, dirty, or obscured** and it will still scan correctly. This is important for physical labels in a warehouse environment.

### Storage Symlink
QR files are stored in `storage/app/public/` which is outside the web root. A **symlink** (shortcut) at `public/storage` points to it, making the files accessible via URL. This was set up with:
```
php artisan storage:link
```

---

## How Passwords are Stored Securely

Passwords are **never stored as plain text**. Laravel uses **bcrypt hashing**:

1. User enters password `"password"`
2. Laravel runs it through bcrypt: `Hash::make('password')`
3. Result stored in DB: `$2y$12$abc123...` (60-character hash)
4. On login: Laravel hashes what the user typed and **compares the hashes** — the original password is never retrieved

Even if someone accessed the database directly, they could not read the passwords.

---
