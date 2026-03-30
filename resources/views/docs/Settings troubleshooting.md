# Settings & Module Detection Troubleshooting

## Issue 1: Modules Not Detected During Installation

### Problem
The installer shows "No modules found" even though modules exist in the `Modules/` directory.

### Causes
1. Module facade not properly initialized
2. Module discovery not run
3. Module structure incorrect

### Solutions

#### Solution 1: Manual Module Scan (Already Implemented)
The installer now scans the `Modules/` directory directly and reads `module.json` files.

**What to check:**
- Modules exist in `Modules/` directory
- Each module has a `module.json` file
- `module.json` has valid JSON structure

Example `module.json`:
```json
{
    "name": "YourModule",
    "alias": "yourmodule",
    "description": "Your module description",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\YourModule\\Providers\\YourModuleServiceProvider"
    ],
    "files": []
}
```

#### Solution 2: Check Module Configuration
Verify `config/modules.php` exists and has correct paths:

```php
return [
    'namespace' => 'Modules',
    'paths' => [
        'modules' => base_path('Modules'),
    ],
    // ... other config
];
```

#### Solution 3: Run Module Discovery
If modules still don't show, after setup run:

```bash
php artisan module:discover
php artisan config:clear
```

#### Solution 4: Check Composer Autoloading
Your `composer.json` should include:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "Modules/"
        }
    }
}
```

Then run:
```bash
composer dump-autoload
```

---

## Issue 2: Settings Table Not Found Error

### Problem
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'connexion.settings' doesn't exist
```

### Cause
Your `AppServiceProvider` tries to access settings before migrations have run.

### Solutions

#### Solution 1: Use InstallationHelper Trait (RECOMMENDED)

Update your `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use App\Helpers\InstallationHelper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use InstallationHelper;

    public function boot(): void
    {
        // Skip during installation
        if ($this->isInstalling()) {
            return;
        }

        // Only load settings if table exists
        if ($this->tableExists('settings')) {
            try {
                if (class_exists(\App\Settings\GeneralSettings::class)) {
                    $settings = new \App\Settings\GeneralSettings();
                    
                    if (property_exists($settings, 'church_abbreviation')) {
                        Config::set('app.name', $settings->church_abbreviation);
                    }
                }
            } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
                // Settings not initialized yet
                \Log::debug('Settings not initialized: ' . $e->getMessage());
            } catch (\Exception $e) {
                \Log::warning('Could not load settings: ' . $e->getMessage());
            }
        }
    }
}
```

#### Solution 2: Initialize Settings After Installation

The installer now automatically runs `settings:init` command after installation. This creates default setting values.

If settings still aren't initialized, run manually:

```bash
php artisan settings:init
```

#### Solution 3: Publish Settings Migrations

If using Spatie Laravel Settings, publish migrations:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate
```

The installer now does this automatically.

---

## Issue 3: MissingSettings Exception

### Problem
```
Spatie\LaravelSettings\Exceptions\MissingSettings: 
There are no settings stored with the class...
```

### Cause
Settings table exists but no settings records created yet.

### Solutions

#### Solution 1: Initialize Settings
```bash
php artisan settings:init
```

#### Solution 2: Create Settings Manually
```php
use App\Settings\GeneralSettings;

$settings = new GeneralSettings();
$settings->church_name = 'Your Church Name';
$settings->church_abbreviation = 'YCN';
$settings->save();
```

#### Solution 3: Use Settings Seeder

Create `database/seeders/SettingsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Settings\GeneralSettings;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = new GeneralSettings();
        $settings->church_name = config('app.name');
        $settings->church_abbreviation = config('app.name');
        $settings->save();
    }
}
```

Run it:
```bash
php artisan db:seed --class=SettingsSeeder
```

---

## Verification Steps

### Check Module Detection
```bash
# List all modules
php artisan module:list

# Check specific module
php artisan module:show YourModule
```

### Check Settings Table
```bash
php artisan tinker
>>> Schema::hasTable('settings')
=> true

>>> DB::table('settings')->get()
```

### Check Settings Class
```bash
php artisan tinker
>>> $settings = new App\Settings\GeneralSettings();
>>> $settings->church_name
```

---

## Prevention: Best Practices

### 1. Always Check Installation Status

```php
if (file_exists(storage_path('installed'))) {
    // Safe to access database
}
```

### 2. Use Try-Catch for Settings

```php
try {
    $settings = new GeneralSettings();
} catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
    // Handle missing settings
}
```

### 3. Verify Table Exists

```php
if (Schema::hasTable('settings')) {
    // Safe to query
}
```

### 4. Use Database Transactions

```php
DB::transaction(function () {
    // Your database operations
});
```

---

## Complete Installation Checklist

After installation completes, verify:

- [ ] `storage/installed` file exists
- [ ] Database tables created (run `php artisan migrate:status`)
- [ ] Settings table populated (check with tinker)
- [ ] Modules detected (run `php artisan module:list`)
- [ ] Admin user created (check users table)
- [ ] Can login to `/admin` panel
- [ ] No errors in `storage/logs/laravel.log`

---

## Manual Fix: Complete Reset

If installation is partially complete and broken:

```bash
# 1. Delete marker files
rm storage/installed
rm storage/setup_complete

# 2. Drop and recreate database
mysql -u root -p
DROP DATABASE connexion;
CREATE DATABASE connexion;
exit

# 3. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload

# 4. Restart installation
# Navigate to /setup.php in browser
```

---

## Debug Mode

Enable debug mode in `.env` to see detailed errors:

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

---

## Getting Help

When reporting issues, provide:

1. **Laravel version:** `php artisan --version`
2. **PHP version:** `php -v`
3. **Module list:** `php artisan module:list`
4. **Migration status:** `php artisan migrate:status`
5. **Error logs:** Last 50 lines of `storage/logs/laravel.log`
6. **Database tables:** List of tables in your database
7. **Settings check:**
   ```bash
   php artisan tinker
   >>> Schema::hasTable('settings')
   >>> DB::table('settings')->count()
   ```