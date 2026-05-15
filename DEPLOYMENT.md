# Deployment Guide — QR Inventory

## How QR Codes Work Online

Each product gets a QR code that encodes a URL:
```
https://yourdomain.com/qr/scan/ELEC-001
```

When someone scans the QR code with any phone camera:
1. Phone opens the URL in the browser
2. Laravel logs the scan (IP, user, timestamp)
3. If the user is logged in → goes straight to the product page
4. If not logged in → redirects to login, then to the product page

The `/qr/scan/{sku}` route is **public** (no auth required) so physical QR
labels always work even on devices that haven't logged in yet.

---

## Before You Deploy

### 1. Set your domain in `.env`
```env
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false
```

### 2. Regenerate all QR codes
After changing APP_URL, regenerate so every QR encodes the live domain:
```bash
php artisan qr:regenerate
```

To regenerate a single product:
```bash
php artisan qr:regenerate --sku=ELEC-001
```

---

## Full Deployment Steps

```bash
# 1. Upload files to server (git pull / FTP / etc.)

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Set environment
cp .env.example .env
# Edit .env: set APP_URL, DB_*, APP_KEY

php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Link storage (for QR images and product photos)
php artisan storage:link

# 6. Regenerate QR codes with live domain
php artisan qr:regenerate

# 7. Seed demo data (optional)
php artisan db:seed --force

# 8. Cache for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Set folder permissions (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Recommended Hosting Options

| Platform       | Notes                                              |
|----------------|----------------------------------------------------|
| **Railway**    | Free tier, auto-deploys from GitHub, easy setup    |
| **Render**     | Free tier, supports PHP via Docker                 |
| **DigitalOcean App Platform** | $5/mo, one-click Laravel deploy   |
| **Shared hosting (cPanel)** | Upload files, point domain to `/public` |
| **VPS (Ubuntu)** | Full control, use Nginx + PHP-FPM               |

### Shared Hosting Note
Point your domain's document root to the `/public` folder, not the project root.

---

## Database Options for Production

The app uses **SQLite** by default (zero config, single file).
For production with multiple users, switch to MySQL/PostgreSQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Then run `php artisan migrate --force`.

---

## After Deployment — Verify QR Codes Work

1. Open your live site and go to any product
2. The QR code image should show `https://yourdomain.com/qr/scan/SKU`
3. Scan it with your phone — it should open the product page
4. Check the dashboard "Recent QR Scans" to confirm the scan was logged
