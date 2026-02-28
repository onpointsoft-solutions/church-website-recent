# PHP 500 Error Fix Guide

## Problem: ALL PHP files return 500 errors

## Root Cause Analysis
Since static files (.txt, .html) work but ALL PHP files fail, this is a **PHP configuration issue**, not code issues.

## Most Likely Causes

### 1. Missing PHP Extensions (Most Common)
Your XAMPP PHP is missing essential extensions.

**Fix:**
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "PHP (php.ini)"
4. Find and uncomment (remove semicolon):
   ```
   extension=mysqli
   extension=pdo_mysql
   extension=pdo
   extension=curl
   extension=openssl
   extension=mbstring
   extension=fileinfo
   ```
5. Save file
6. Restart Apache

### 2. PHP Version Mismatch
XAMPP might be running different PHP version than expected.

**Check:** Access `debug.php` to see actual PHP version and loaded extensions.

### 3. Corrupt XAMPP Installation
PHP installation itself may be corrupted.

**Fix:** Reinstall XAMPP completely.

### 4. Apache Configuration Issues
Apache might not be configured to handle .php files properly.

**Check:** Apache httpd.conf file for PHP handler configuration.

## Immediate Action Plan

1. **Test debug.php**: `http://localhost/church-website-recent/bible_study/debug.php`
   - This will show exactly what's wrong
   - Look for missing extensions or include errors

2. **If debug.php works**: Follow extension fix steps above

3. **If debug.php fails**: Reinstall XAMPP

## Quick Test Commands

```bash
# Check PHP version
php -v

# Check loaded extensions
php -m

# Check php.ini location
php --ini
```

## Alternative: Use Different Server Stack

If XAMPP continues to fail, consider:
- Laragon
- WampServer
- Docker setup

## Admin User Creation (Once PHP Fixed)

After fixing PHP, use this SQL to create admin:

```sql
INSERT INTO bs_users (name, email, password, role, status, verified, created_at) 
VALUES ('Test Admin', 'testadmin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1, NOW());
```

**Credentials:**
- Email: testadmin@gmail.com
- Password: Admin@321
