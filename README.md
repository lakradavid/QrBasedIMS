# QR-Based Inventory Management System

A web-based inventory management system built with **Laravel 11** that uses **QR codes** to track products, manage stock levels, and log every inventory movement.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Tech Stack](#2-tech-stack)
3. [System Architecture](#3-system-architecture)
4. [Database Design](#4-database-design)
5. [Features](#5-features)
6. [How It Works — Module by Module](#6-how-it-works--module-by-module)
7. [QR Code Lifecycle](#7-qr-code-lifecycle)
8. [Authentication & Security](#8-authentication--security)
9. [File & Storage Structure](#9-file--storage-structure)
10. [Installation & Setup](#10-installation--setup)
11. [Key URLs / Routes](#11-key-urls--routes)

---

## 1. Project Overview

This system solves the problem of manual, error-prone inventory tracking by assigning every product a unique QR code. Staff can scan a QR code with any device to instantly view a product, log stock movements, or perform quick stock-in/out operations — without typing anything.

**Core problems it solves:**
- Real-time stock visibility across locations
- Full audit trail of every stock movement
- Instant product lookup via QR scan
- Low stock alerts before items run out
- Exportable reports for analysis

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 11 (PHP) |
| Database | SQLite (local) / MySQL (production) |
| Frontend | Blade templates + Tailwind CSS + Alpine.js |
| QR Generation | endroid/qr-code (server-side SVG) |
| QR Scanning (camera) | ZXing JavaScript library |
| QR Scanning (image upload) | jsQR JavaScript library |
| Authentication | Laravel Breeze |
| Build Tool | Vite |

---

## 3. System Architecture

```
Browser (User)
     │
     ▼
┌─────────────────────────────────────────┐
│           Laravel Application           │
│                                         │
│  Routes (web.php)                       │
│       │                                 │
│       ▼                                 │
│  Controllers  ──►  Services             │
│  │                 (QrCodeService)      │
│  ▼                                      │
│  Models (Eloquent ORM)                  │
│  │                                      │
│  ▼                                      │
│  Database (SQLite / MySQL)              │
└─────────────────────────────────────────┘
     │
     ▼
Storage (public/storage/qrcodes/*.svg)
```

**Request flow:**
1. User visits a URL in the browser
2. Laravel Router matches it to a Controller method
3. Controller calls Models to read/write the database
4. For QR generation, Controller calls `QrCodeService`
5. Controller returns a Blade view (HTML) back to the browser

---

## 4. Database Design

The database has **8 tables**:

```
users
  └── id, name, email, password, timestamps

categories
  └── id, name, slug, description, color, timestamps

locations
  └── id, name, code, description, building, floor, aisle, timestamps

products
  ├── id, name, sku (unique), barcode
  ├── category_id → categories
  ├── location_id → locations
  ├── price, cost, quantity, min_quantity, unit
  ├── image (file path), qr_code (file path)
  ├── status (active / inactive / discontinued)
  ├── deleted_at (soft delete)
  └── timestamps

inventory_transactions
  ├── id, product_id → products
  ├── user_id → users
  ├── type (in / out / adjustment / transfer)
  ├── quantity, quantity_before, quantity_after
  ├── from_location_id → locations
  ├── to_location_id → locations
  ├── reference, notes
  └── timestamps

qr_scans
  ├── id, product_id → products
  ├── user_id → users
  ├── action, ip_address, user_agent
  └── timestamps

cache          (Laravel session cache)
jobs           (Laravel queue jobs)
```

**Key relationships:**
- A **Product** belongs to one Category and one Location
- A **Product** has many InventoryTransactions (full history)
- A **Product** has many QrScans (scan analytics)
- Every stock change creates an **InventoryTransaction** record

---

## 5. Features

### Product Management
- Create, edit, delete products with image upload
- Each product gets a unique SKU and auto-generated QR code
- Soft delete (products are archived, not permanently removed)
- Search by name, SKU, or barcode
- Filter by category, status, or stock level

### Inventory Control
- **Stock In** — add quantity (e.g. receiving a delivery)
- **Stock Out** — remove quantity (e.g. issuing to a department)
- **Adjustment** — set exact quantity (e.g. after a physical count)
- **Transfer** — move product between locations
- Every action records before/after quantities and the user who did it

### QR Code System
- QR codes are generated as SVG files on the server
- Each QR encodes the URL: `http://yourapp.com/qr/scan/{SKU}`
- Scanning redirects to the product page and logs the scan
- Download QR as SVG (vector) or PNG (raster)
- Bulk print multiple QR codes at once
- Upload a QR image to decode and open the product

### QR Scanner (Browser)
- Live camera scanner using ZXing
- Image upload decoder using jsQR
- Manual SKU lookup fallback

### Dashboard
- Total products, active products, low stock count, out-of-stock count
- Total inventory value (quantity × cost)
- Recent transactions feed
- Low stock alerts list
- Recent QR scan activity
- 7-day transaction chart (stock in vs out)

### Reports
- Stock value breakdown by category
- 30-day transaction volume chart
- Top 10 most-moved products
- Low stock and out-of-stock lists
- Export products to CSV
- Export transactions to CSV (with date range filter)

### Categories & Locations
- Organise products into colour-coded categories
- Define physical locations with building/floor/aisle detail

---

## 6. How It Works — Module by Module

### Products (`ProductController`)

```
GET  /products          → list with search/filter/pagination
GET  /products/create   → create form
POST /products          → validate → save → generate QR → redirect
GET  /products/{id}     → detail page with stock actions + transaction history
GET  /products/{id}/edit → edit form
PUT  /products/{id}     → validate → update → redirect
DELETE /products/{id}   → soft delete → redirect
POST /products/{id}/regenerate-qr → delete old QR → generate new → redirect
GET  /products/{id}/download-qr     → download SVG file
GET  /products/{id}/download-qr-png → convert SVG to PNG → download
```

### Inventory (`InventoryController`)

Every stock action follows the same pattern:
1. Validate the request
2. Load the product
3. Calculate new quantity
4. Update `products.quantity`
5. Insert a row into `inventory_transactions` with before/after values
6. Redirect back with a success message

```
POST /inventory/stock-in   → quantity += input
POST /inventory/stock-out  → quantity -= input (checks sufficient stock)
POST /inventory/adjust     → quantity = input (manual override)
POST /inventory/transfer   → update location_id, log transfer record
```

### QR Codes (`QrController` + `QrCodeService`)

**Generation** (server-side, PHP):
```
QrCodeService::generate($product)
  → builds URL: APP_URL + /qr/scan/ + SKU
  → uses endroid/qr-code to render SVG
  → saves to storage/app/public/qrcodes/qr-{SKU}.svg
  → returns relative path stored in products.qr_code
```

**Scanning** (public route, no login required):
```
GET /qr/scan/{sku}
  → find product by SKU
  → log scan to qr_scans table (IP, user agent, timestamp)
  → redirect to /products/{id}
```

**Browser scanner** (JavaScript, client-side):
```
Camera mode  → ZXing BrowserMultiFormatReader → decodes live video frames
Upload mode  → jsQR → loads image onto canvas → synchronous decode
Manual mode  → user types SKU → redirect to /qr/scan/{sku}
```

### Reports (`ReportController`)

All report data is computed with SQL aggregations:
- `SUM(quantity * cost)` → stock value
- `SUM(ABS(quantity)) GROUP BY date, type` → transaction chart
- `whereColumn('quantity', '<=', 'min_quantity')` → low stock

CSV exports stream directly from the controller — no temporary files.

---

## 7. QR Code Lifecycle

```
1. Product created
        │
        ▼
2. QrCodeService generates SVG
   (encodes URL: /qr/scan/{SKU})
        │
        ▼
3. SVG saved to storage/qrcodes/
   Path stored in products.qr_code
        │
        ▼
4. QR displayed on product page
   Can be downloaded as SVG or PNG
   Can be printed (bulk print page)
        │
        ▼
5. Physical QR label attached to item
        │
        ▼
6. Staff scans QR with phone/scanner
        │
        ▼
7. Browser opens /qr/scan/{SKU}
        │
        ▼
8. Server logs scan → qr_scans table
   Redirects to product detail page
        │
        ▼
9. Staff performs stock action
   (in / out / adjust / transfer)
        │
        ▼
10. InventoryTransaction recorded
    Product quantity updated
```

---

## 8. Authentication & Security

- Built on **Laravel Breeze** (register, login, password reset, email verification)
- All inventory routes require authentication (`middleware('auth')`)
- The QR scan route (`/qr/scan/{sku}`) is **public** — physical QR codes must work without login
- Passwords are hashed with bcrypt (12 rounds)
- CSRF protection on all forms
- Input validation on every controller action
- Soft deletes prevent accidental permanent data loss

---

## 9. File & Storage Structure

```
inventory-qr/
├── app/
│   ├── Http/Controllers/     ← request handling logic
│   ├── Models/               ← database models (Eloquent)
│   └── Services/
│       └── QrCodeService.php ← QR generation logic
├── database/
│   ├── migrations/           ← table definitions
│   └── database.sqlite       ← the actual database file
├── resources/views/
│   ├── dashboard.blade.php
│   ├── products/             ← index, show, create, edit
│   ├── inventory/            ← transaction list
│   ├── qr/                   ← scanner, bulk-print, quick-action
│   ├── categories/
│   ├── locations/
│   └── reports/
├── routes/
│   └── web.php               ← all URL definitions
├── storage/app/public/
│   ├── qrcodes/              ← generated QR SVG files
│   └── products/             ← uploaded product images
└── public/storage/           ← symlink to storage/app/public
```

---

## 10. Installation & Setup

```bash
# 1. Clone the repository
git clone https://github.com/lakradavid/QrBasedIMS.git
cd QrBasedIMS

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies and build assets
npm install && npm run build

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Create and migrate the database
touch database/database.sqlite
php artisan migrate

# 6. Link storage (makes QR images publicly accessible)
php artisan storage:link

# 7. Start the development server
php artisan serve
```

Open `http://localhost:8000` in your browser.

**To switch to MySQL**, update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
Then run `php artisan migrate`.

---

## 11. Key URLs / Routes

| URL | Description | Auth |
|---|---|---|
| `/` | Dashboard | ✅ |
| `/products` | Product list | ✅ |
| `/products/create` | Add product | ✅ |
| `/products/{id}` | Product detail + stock actions | ✅ |
| `/inventory` | Transaction history | ✅ |
| `/qr/scanner` | Live camera + upload QR scanner | ✅ |
| `/qr/bulk-print?ids[]=1&ids[]=2` | Print multiple QR labels | ✅ |
| `/qr/scan/{sku}` | QR scan handler (public) | ❌ |
| `/categories` | Manage categories | ✅ |
| `/locations` | Manage locations | ✅ |
| `/reports` | Reports & analytics | ✅ |
| `/reports/export/products` | Download products CSV | ✅ |
| `/reports/export/transactions` | Download transactions CSV | ✅ |

---

## License

MIT License — open source, free to use and modify.
