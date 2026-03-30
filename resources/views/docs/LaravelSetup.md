# Laravel 12 Installer Setup Instructions

## Step 1: Add the Installer Files

Copy all installer files to your Laravel 12 project:

```
app/
  └── Http/
      ├── Controllers/
      │   └── InstallerController.php
      └── Middleware/
          └── InstallationMiddleware.php

resources/
  └── views/
      └── installer/
          ├── layout.blade.php
          ├── welcome.blade.php
          ├── requirements.blade.php
          ├── permissions.blade.php
          ├── environment.blade.php
          ├── database.blade.php
          ├── modules.blade.php
          ├── admin.blade.php
          ├── settings.blade.php
          └── final.blade.php
```

## Step 2: Add Installer Routes

Create a new file `routes/installer.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallerController;

Route::prefix('install')->middleware('web')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('installer.welcome');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('installer.requirements');
    Route::get('/permissions', [InstallerController::class, 'permissions'])->name('installer.permissions');
    Route::get('/environment', [InstallerController::class, 'environment'])->name('installer.environment');
    Route::post('/environment', [InstallerController::class, 'saveEnvironment'])->name('installer.environment.save');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('installer.database.test');
    Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('installer.database.save');
    Route::get('/modules', [InstallerController::class, 'modules'])->name('installer.modules');
    Route::post('/modules', [InstallerController::class, 'saveModules'])->name('installer.modules.save');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('installer.admin');
    Route::post('/admin', [InstallerController::class, 'saveAdmin'])->name('installer.admin.save');
    Route::get('/settings', [InstallerController::class, 'settings'])->name('installer.settings');
    Route::post('/settings', [InstallerController::class, 'saveSettings'])->name('installer.settings.save');
    Route::get('/final', [InstallerController::class, 'final'])->name('installer.final');
    Route::post('/finalize', [InstallerController::class, 'finalize'])->name('installer.finalize');
});
```

## Step 3: Register Middleware and Routes in bootstrap/app.php

**This is the Laravel 12 way!** Edit `bootstrap/app.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\InstallationMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // IMPORTANT: Load installer routes FIRST before web routes
            if (file_exists(base_path('routes/installer.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/installer.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add installation check middleware to web group
        $middleware->web(append: [
            InstallationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**Important:** Make sure to add `use Illuminate\Support\Facades\Route;` at the top if not already present.

## Step 4: Fix Your AppServiceProvider for Installation

The installer needs to prevent your AppServiceProvider from trying to access the database before installation is complete. 

### Option 1: Use the InstallationHelper Trait (Recommended)

1. Create `app/Helpers/InstallationHelper.php` (provided in artifacts)
2. Update your `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use App\Helpers\InstallationHelper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use InstallationHelper;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Skip all database operations during installation
        if ($this->isInstalling()) {
            return;
        }

        // Only load settings if database is accessible and table exists
        if ($this->tableExists('settings')) {
            try {
                $settings = new \App\Settings\GeneralSettings();
                Config::set('app.name', $settings->church_abbreviation);
            } catch (\Exception $e) {
                \Log::warning('Could not load settings: ' . $e->getMessage());
            }
        }
    }
}
```

### Option 2: Simple Installation Check

If you prefer not to use a trait, wrap your database code:

```php
public function boot(): void
{
    // Check if installation is complete
    if (!file_exists(storage_path('installed'))) {
        return;
    }

    // Check if on installer routes
    if (request()->is('install') || request()->is('install/*')) {
        return;
    }

    // Now safe to access database
    try {
        $settings = new GeneralSettings();
        if (Schema::hasTable('settings')) {
            Config::set('app.name', $settings->church_abbreviation);
        }
    } catch (\Exception $e) {
        \Log::warning('Could not load settings: ' . $e->getMessage());
    }
}
```

## Step 5: Ensure .env File Exists

**Good news!** The installer now automatically creates `.env` from `.env.example` if it doesn't exist.

But you can still manually create it if needed:

```bash
# If .env doesn't exist, create it
cp .env.example .env

# Generate application key if not set
php artisan key:generate
```

## Step 6: Set Proper Permissions

```bash
# Make sure these directories are writable
chmod -R 775 storage bootstrap/cache

# Change owner to your web server user (adjust as needed)
chown -R www-data:www-data storage bootstrap/cache
```

**For macOS/Linux development:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

**For Windows (in PowerShell as Administrator):**
```powershell
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

## Step 7: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Step 8: Access the Installer

Start your development server:

```bash
php artisan serve
```

Then navigate to:
```
http://localhost:8000/install
```

Or if using Valet, Herd, or another local dev environment:
```
http://your-domain.test/install
```

## Troubleshooting

### "Call to a member function getName() on null"

This happens when modules aren't configured yet. The updated controller handles this gracefully. Make sure you're using the latest version of the InstallerController.

### Routes not found

Make sure you've added the installer routes in `bootstrap/app.php` as shown in Step 3.

### Middleware not working

In Laravel 12, middleware MUST be registered in `bootstrap/app.php`, not in a Kernel.php file.

### Can't write to storage

Check permissions:
```bash
ls -la storage/
ls -la bootstrap/cache/
```

Both should be writable by your web server user.

### Database connection fails

- Verify your database credentials
- Make sure the database exists
- Check that your database server is running
- For MySQL: `mysql -u root -p` to test connection

## After Installation

Once installed successfully:

1. Delete `storage/installed` to allow reinstallation (if needed)
2. Consider removing installer routes from production
3. Log in to your admin panel at `/admin`

## Security Note

For production deployments:

1. Remove or disable installer routes after installation
2. Delete the installer views and controller
3. Remove the InstallationMiddleware registration
4. Keep the `storage/installed` file to prevent reinstallation

## Laravel 12 Specific Notes

- No `app/Http/Kernel.php` file exists
- No `app/Providers/RouteServiceProvider.php` file exists
- All middleware registration happens in `bootstrap/app.php`
- Routes are loaded via the `->withRouting()` method
- Use the `then` parameter to load custom route files