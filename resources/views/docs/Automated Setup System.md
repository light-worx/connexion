# Automated Setup System

## Overview

The Connexion installer now includes an **automated pre-installation setup** that runs when users first access the application. This eliminates most manual setup steps and makes deployment as simple as:

1. Download/clone the repository
2. Upload to web server
3. Navigate to the URL
4. Follow the automated setup

## What Gets Automated

### Phase 1: Pre-Installation Setup (`setup.php`)
Runs **before** Laravel boots. Handles:

- ✅ **File Permissions** - Automatically sets correct permissions on `storage/` and `bootstrap/cache/`
- ✅ **Environment File** - Creates `.env` from `.env.example` if missing
- ✅ **Composer Dependencies** - Runs `composer install` to install all packages
- ✅ **Application Key** - Generates a secure `APP_KEY` for encryption

### Phase 2: Application Installation (`/install`)
Runs after Laravel is bootable. Handles:

- Database configuration and testing
- Module selection
- Admin user creation
- Application settings
- Database migrations

## File Structure

```
public/
  ├── index.php          # Modified to check setup status
  └── setup.php          # New pre-installation setup script

app/
  └── Http/
      └── Middleware/
          └── InstallationMiddleware.php  # Updated to check setup_complete

storage/
  ├── setup_complete     # Marker: basic setup done
  └── installed          # Marker: full installation done
```

## How It Works

### 1. First Access

When a user navigates to the site for the first time:

```
User visits http://yoursite.com
         ↓
index.php checks for requirements
         ↓
Missing requirements? → Redirect to setup.php
         ↓
setup.php shows setup interface
         ↓
User clicks "Start Setup"
         ↓
Automated setup runs:
  - Fix permissions
  - Create .env
  - Install composer dependencies
  - Generate APP_KEY
         ↓
Creates storage/setup_complete marker
         ↓
Redirects to /install
         ↓
Laravel installer runs
```

### 2. Setup.php Interface

Beautiful, user-friendly interface showing:
- Real-time progress for each step
- Visual indicators (pending, running, success, error)
- Detailed error messages if something fails
- Option to retry failed steps

### 3. Smart Detection

`index.php` automatically detects if setup is needed by checking:
- `.env` file exists
- `vendor/autoload.php` exists (dependencies installed)
- `storage/` and `bootstrap/cache/` are writable
- `APP_KEY` is set in `.env`

## Installation Flow

### For End Users

1. **Download** the application package
2. **Upload** to web hosting (via FTP, cPanel, etc.)
3. **Navigate** to the URL in a browser
4. **Click** "Start Setup" button
5. **Wait** for automated setup (30-60 seconds)
6. **Proceed** to Laravel installer
7. **Complete** database and admin setup
8. **Done!** 🎉

### For Developers

Same as end users, but you can also:

```bash
# Manual setup if preferred
cp .env.example .env
composer install
php artisan key:generate
chmod -R 775 storage bootstrap/cache

# Then access /install
php artisan serve
```

## Requirements

### Server Requirements
- PHP 8.2 or higher
- Shell access for `exec()` function (for composer)
- Write permissions on project directory

### PHP Extensions
All checked during setup:
- OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, cURL, Fileinfo, GD, Zip

### Optional
- `shell_exec()` enabled (for composer automation)
- Composer installed globally

## What If Composer Fails?

If the server doesn't have Composer or `exec()` is disabled:

1. Setup.php will show an error message
2. User can install dependencies manually:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Re-run setup.php or proceed to /install

## Troubleshooting

### "Composer not found"

**Solution 1 - Install Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

**Solution 2 - Manual Install:**
Download dependencies on your local machine:
```bash
composer install --no-dev
```
Then upload the entire `vendor/` directory to your server.

### "Permission denied"

The script tries to fix permissions automatically, but if it fails:

```bash
# Via SSH
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Via cPanel
Use File Manager → Select folders → Change Permissions to 775
```

### "Setup already complete" but site won't load

The `storage/setup_complete` file exists but something else failed:

1. Delete `storage/setup_complete`
2. Refresh the page
3. Setup will run again

### Can't access setup.php

Check your web server configuration:
- Apache: `.htaccess` should allow direct PHP file access
- Nginx: Add location block for setup.php

```nginx
location = /setup.php {
    try_files $uri =404;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index setup.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## Security Considerations

### Production Deployment

After installation is complete, for production sites:

1. **Delete setup.php:**
   ```bash
   rm public/setup.php
   ```

2. **Disable dangerous functions** in php.ini (if not needed):
   ```ini
   disable_functions = exec,shell_exec,system,passthru
   ```

3. **Set proper permissions:**
   ```bash
   chmod 644 .env
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   ```

4. **Keep marker files:**
   - Keep `storage/setup_complete`
   - Keep `storage/installed`
   - These prevent re-installation

### Development

For development environments:
- Keep setup.php available
- You can delete marker files to re-run setup
- Set `APP_ENV=local` and `APP_DEBUG=true`

## Resetting Everything

To start fresh:

```bash
# Delete marker files
rm storage/setup_complete
rm storage/installed

# Delete vendor (optional - forces reinstall)
rm -rf vendor

# Delete .env (optional - forces recreation)
rm .env

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Refresh browser
# Setup will start automatically
```

## Advanced: Skipping Automated Setup

If you prefer manual setup:

1. **Create marker file first:**
   ```bash
   touch storage/setup_complete
   ```

2. **Do manual setup:**
   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   chmod -R 775 storage bootstrap/cache
   ```

3. **Access installer:**
   Navigate to `/install`

## Customization

### Modify Setup Steps

Edit `public/setup.php`:

```php
// Add custom checks
$criticalPaths = [
    'storage' => $basePath . '/storage',
    'bootstrap/cache' => $basePath . '/bootstrap/cache',
    'uploads' => $basePath . '/public/uploads', // Add custom path
];

// Add custom setup steps
$setupSteps['custom'] = [
    'status' => 'pending', 
    'message' => 'Running custom setup...'
];
```

### Modify Detection Logic

Edit `public/index.php`:

```php
// Add custom checks
if (!file_exists($basePath . '/public/uploads')) {
    $needsSetup = true;
    $setupReasons[] = 'Upload directory missing';
}
```

## Benefits

### For Users
- ✅ No terminal/SSH required
- ✅ No technical knowledge needed
- ✅ Visual progress feedback
- ✅ Clear error messages
- ✅ One-click solution

### For Developers
- ✅ Less support requests
- ✅ Faster deployments
- ✅ Consistent setup process
- ✅ Easy to customize
- ✅ Works on shared hosting

### For Hosting
- ✅ Works on shared hosting
- ✅ No shell access needed
- ✅ cPanel compatible
- ✅ Automatic permission fixing

## Best Practices

1. **Test on clean environment** before deploying
2. **Document any custom setup steps** users might need
3. **Provide fallback instructions** for manual setup
4. **Keep setup.php** during beta/testing phase
5. **Remove setup.php** for production release
6. **Version control** the `.env.example` file
7. **Don't commit** the marker files to git

## Support

If users encounter issues:
1. Check `storage/logs/laravel.log`
2. Verify PHP version and extensions
3. Test Composer availability
4. Check file permissions manually
5. Try manual setup as fallback