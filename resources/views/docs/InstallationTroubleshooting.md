# Installation Troubleshooting Guide

## Common Issues and Solutions

### 1. "Call to a member function getName() on null"

**Cause:** Modules system not configured or returning null modules.

**Solution:** The updated `InstallerController` handles this automatically. Make sure you're using the latest version.

---

### 2. "could not find driver (Connection: sqlite, SQL: select exists...)"

**Cause:** Your `AppServiceProvider` is trying to access the database before installation is complete, and the SQLite driver isn't available or the database file doesn't exist yet.

**Solutions:**

**Option A - Use InstallationHelper Trait (Recommended):**
```php
use App\Helpers\InstallationHelper;

class AppServiceProvider extends ServiceProvider
{
    use InstallationHelper;

    public function boot(): void
    {
        if ($this->isInstalling()) {
            return;
        }

        if ($this->tableExists('settings')) {
            // Your settings code here
        }
    }
}
```

**Option B - Manual Check:**
```php
public function boot(): void
{
    // Skip during installation
    if (!file_exists(storage_path('installed')) || 
        request()->is('install*')) {
        return;
    }

    // Your database code here
}
```

---

### 3. ".env file not found"

**Cause:** Missing `.env` file.

**Solution:** The installer now automatically creates it from `.env.example`. If you still see this error:

```bash
cp .env.example .env
php artisan key:generate
```

---

### 4. "Permission denied" when writing to .env

**Cause:** `.env` file is not writable by the web server.

**Solution:**
```bash
chmod 644 .env
# Or if you need write access
chmod 664 .env
```

---

### 5. Routes not found / 404 on /install

**Cause:** Routes not properly registered in `bootstrap/app.php`.

**Solution:** Make sure your `bootstrap/app.php` includes:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        require base_path('routes/installer.php');
    }
)
```

Then clear routes:
```bash
php artisan route:clear
php artisan route:list | grep install
```

---

### 6. Database connection test fails

**Common Causes:**

**MySQL:**
- Database server not running
- Wrong credentials
- Database doesn't exist
- Wrong port (default: 3306)

```bash
# Test MySQL connection
mysql -h 127.0.0.1 -u root -p
# Then create database
CREATE DATABASE connexion;
```

**PostgreSQL:**
- Wrong port (default: 5432)
- Database doesn't exist
- User doesn't have permissions

```bash
# Test PostgreSQL connection
psql -h 127.0.0.1 -U postgres
# Create database
CREATE DATABASE connexion;
```

**SQLite:**
- Make sure directory is writable
- For production, use MySQL/PostgreSQL instead

```bash
# Create SQLite database
touch database/database.sqlite
chmod 664 database/database.sqlite
```

---

### 7. "Class 'PDO' not found"

**Cause:** PDO extension not installed.

**Solution:**

**Ubuntu/Debian:**
```bash
sudo apt-get install php8.2-pdo php8.2-mysql
sudo systemctl restart apache2
# or
sudo systemctl restart php8.2-fpm
```

**macOS:**
```bash
brew install php@8.2
brew services restart php@8.2
```

**Windows:**
- Edit `php.ini`
- Uncomment: `extension=pdo_mysql`
- Restart web server

---

### 8. Migrations fail during installation

**Cause:** Database connection issues or migration conflicts.

**Solution:**

1. Test database connection manually
2. Check logs: `storage/logs/laravel.log`
3. Try running migrations manually:
```bash
php artisan migrate:fresh --force
```

4. If specific migration fails, check the migration file for syntax errors

---

### 9. "Target class [App\Settings\GeneralSettings] does not exist"

**Cause:** Settings class not found or namespace incorrect.

**Solution:**

1. Check if the class exists at the correct path
2. Make sure namespace matches file location
3. Run composer autoload:
```bash
composer dump-autoload
```

4. Update your AppServiceProvider to handle missing class:
```php
if (class_exists(\App\Settings\GeneralSettings::class)) {
    $settings = new \App\Settings\GeneralSettings();
    // ...
}
```

---

### 10. Installation completes but can't login to admin

**Cause:** User created but Filament not configured properly.

**Solutions:**

1. Check if user was created:
```bash
php artisan tinker
>>> App\Models\User::all();
```

2. Check Filament configuration in `config/filament.php`

3. Make sure you're accessing the correct admin path (usually `/admin`)

4. Clear all caches:
```bash
php artisan optimize:clear
```

---

## Debugging Tips

### Enable Debug Mode
Edit `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Test Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Verify Requirements
```bash
php -v                    # Check PHP version
php -m                    # List installed extensions
php artisan about         # Laravel environment info
```

### Clear Everything
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

---

## Still Having Issues?

1. Check the exact error message in `storage/logs/laravel.log`
2. Verify all requirements are met (PHP version, extensions)
3. Test database connection outside Laravel
4. Make sure all file permissions are correct
5. Try a fresh installation on a clean database

---

## Getting Help

When asking for help, provide:
- Laravel version: `php artisan --version`
- PHP version: `php -v`
- Database type and version
- Exact error message
- Relevant log entries from `storage/logs/laravel.log`
- Steps to reproduce the issue