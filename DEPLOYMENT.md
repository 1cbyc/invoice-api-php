# Laravel Invoice API - cPanel Deployment Guide

## Prerequisites
- cPanel hosting account with PHP 8.1+ support
- MySQL database access
- SSH access (optional but recommended)
- Composer installed on cPanel (or use local build)

---

## Step 1: Prepare Production Files Locally

### 1.1 Update Environment Settings
Your `.env` file should NOT be uploaded. You'll create a new one on the server.

### 1.2 Optimize for Production
Run these commands locally before uploading:

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Install production dependencies only
composer install --optimize-autoloader --no-dev
```

### 1.3 Create Deployment Package
Create a ZIP file with these folders/files:
- `app/`
- `bootstrap/`
- `config/`
- `database/` (migrations, seeders, factories)
- `public/`
- `resources/`
- `routes/`
- `storage/` (keep directory structure, but empty logs)
- `vendor/` (if composer not available on server)
- `artisan`
- `composer.json`
- `composer.lock`
- `.htaccess` (in public folder)

**DO NOT INCLUDE:**
- `.env` (create new on server)
- `.git/`
- `node_modules/`
- `tests/`
- `*.ps1` files
- `screenshots/`

---

## Step 2: cPanel Database Setup

### 2.1 Create MySQL Database
1. Log into cPanel
2. Go to **MySQL Databases**
3. Create new database: `youruser_invoicedb`
4. Create new user: `youruser_apiuser`
5. Set a strong password
6. Add user to database with **ALL PRIVILEGES**
7. **Save these credentials** - you'll need them for `.env`

---

## Step 3: Upload Files to cPanel

### 3.1 Using File Manager
1. In cPanel, go to **File Manager**
2. Navigate to `public_html/`
3. Create a new folder: `invoice-api/` (or your preferred name)
4. Upload your ZIP file
5. Extract the ZIP file
6. You should now have this structure:
   ```
   public_html/
   └── invoice-api/
       ├── app/
       ├── bootstrap/
       ├── config/
       ├── database/
       ├── public/
       ├── resources/
       ├── routes/
       ├── storage/
       ├── vendor/
       └── artisan
   ```

### 3.2 Alternative: Using FTP
Use FileZilla or similar FTP client with your cPanel FTP credentials.

---

## Step 4: Configure Environment File

### 4.1 Create .env File
In `public_html/invoice-api/`, create a new `.env` file:

```env
APP_NAME="Invoice API"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=youruser_invoicedb
DB_USERNAME=youruser_apiuser
DB_PASSWORD=your_database_password

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (optional - for invoice emails)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.2 Generate Application Key
If you have SSH access:
```bash
cd public_html/invoice-api
php artisan key:generate
```

Or manually generate a key:
```bash
# On your local machine
php artisan key:generate --show
```
Copy the key and paste it in `.env` as `APP_KEY=base64:...`

---

## Step 5: Set Directory Permissions

### 5.1 Set Storage Permissions
These folders need write access (755 or 775):
```
storage/
storage/app/
storage/framework/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
storage/logs/
bootstrap/cache/
```

In cPanel File Manager:
1. Right-click each folder
2. Select **Change Permissions**
3. Set to `755` or `775`

---

## Step 6: Configure Document Root

### 6.1 Point Domain to Public Folder

**Option A: Subdomain (Recommended for API)**
1. cPanel → **Subdomains**
2. Create subdomain: `api.yourdomain.com`
3. Set document root to: `public_html/invoice-api/public`

**Option B: Main Domain**
1. cPanel → **Domains** → **Domains**
2. Find your domain → click **Manage**
3. Change **Document Root** to: `public_html/invoice-api/public`

**Option C: Using .htaccess in root**
If you can't change document root, add this to `public_html/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ invoice-api/public/$1 [L]
</IfModule>
```

---

## Step 7: Run Database Migrations

### 7.1 Via SSH (Recommended)
```bash
cd ~/public_html/invoice-api
php artisan migrate --force
php artisan db:seed --class=InvoiceSeeder
```

### 7.2 Via cPanel Terminal
Some cPanel hosts have Terminal access:
1. cPanel → **Terminal**
2. Run the same commands above

### 7.3 Manual Method (If No SSH)
1. Export your local database:
   ```bash
   php artisan migrate:fresh --seed
   mysqldump -u root -p invoice_db > invoice_db.sql
   ```
2. Import via cPanel → **phpMyAdmin**:
   - Select your database
   - Click **Import**
   - Upload `invoice_db.sql`

---

## Step 8: Configure PHP Settings

### 8.1 Check PHP Version
cPanel → **Select PHP Version**
- Ensure PHP 8.1 or higher is selected
- Enable these extensions:
  - mysqli
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - curl
  - gd
  - zip
  - fileinfo

### 8.2 Adjust PHP Limits (Optional)
cPanel → **MultiPHP INI Editor**
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
```

---

## Step 9: Test Your API

### 9.1 Test Endpoints
```bash
# Check if API is accessible
curl https://yourdomain.com/api/register

# Register a user
curl -X POST https://yourdomain.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 9.2 Expected Response
You should get a JSON response with a token.

---

## Step 10: SSL Certificate (HTTPS)

### 10.1 Enable SSL
cPanel → **SSL/TLS Status**
- Enable AutoSSL for your domain
- Or install Let's Encrypt certificate

### 10.2 Force HTTPS
Add to `public/.htaccess` (after `RewriteEngine On`):
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Troubleshooting

### Issue: 500 Internal Server Error
1. Check storage permissions (775)
2. Verify `.env` file exists and has correct credentials
3. Check error logs: `storage/logs/laravel.log`
4. Or cPanel → **Errors** → check Apache error log

### Issue: Database Connection Failed
1. Verify database credentials in `.env`
2. Check DB_HOST (try `localhost` or `127.0.0.1`)
3. Confirm database user has privileges
4. Test connection via phpMyAdmin

### Issue: Routes Not Working
1. Check `.htaccess` file exists in `public/` folder
2. Verify mod_rewrite is enabled
3. Clear route cache: `php artisan route:clear`

### Issue: App Key Missing
```bash
php artisan key:generate --force
```

### Issue: Composer Dependencies
If `vendor/` folder is missing:
```bash
cd ~/public_html/invoice-api
composer install --optimize-autoloader --no-dev
```

---

## Production Checklist

- [ ] `.env` file created with correct settings
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Database created and user assigned
- [ ] Migrations run successfully
- [ ] Storage directories writable (755/775)
- [ ] SSL certificate installed
- [ ] Document root points to `public/` folder
- [ ] PHP 8.1+ with required extensions
- [ ] Tested registration endpoint
- [ ] Tested authentication flow
- [ ] Tested invoice creation
- [ ] Tested PDF generation
- [ ] Email configuration (if using)

---

## Maintenance Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Security Tips

1. **Never commit .env to Git**
2. **Use strong database passwords**
3. **Keep APP_DEBUG=false in production**
4. **Enable HTTPS only**
5. **Regularly update dependencies**: `composer update`
6. **Monitor logs**: `storage/logs/laravel.log`
7. **Backup database regularly**
8. **Rate limit API endpoints** (already configured)

---

## Support

Issues with deployment? Check:
1. Laravel docs: https://laravel.com/docs/deployment
2. GitHub issues: https://github.com/1cbyc/invoice-api-php/issues
3. Twitter: [@1cbyc](https://x.com/1cbyc)

---

## Quick Deployment Script (SSH Access)

Save this as `deploy.sh` on your server:

```bash
#!/bin/bash

# Navigate to project
cd ~/public_html/invoice-api

# Pull latest changes (if using git)
# git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 755 storage bootstrap/cache

echo "Deployment complete!"
```

Make executable: `chmod +x deploy.sh`
Run: `./deploy.sh`
