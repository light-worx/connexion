# Installing Connexion in a Subfolder

## Overview

Connexion can be installed in a subfolder (e.g., `http://localhost/connexion` or `https://example.com/myapp`). The installer automatically detects the subfolder and adjusts all URLs accordingly.

## Supported Configurations

✅ Root installation: `https://example.com/`
✅ Subfolder: `https://example.com/connexion/`
✅ Deep subfolder: `https://example.com/apps/connexion/`
✅ Localhost development: `http://localhost/connexion/`
✅ Custom port: `http://localhost:8080/connexion/`

## Installation Steps

### Option 1: Standard Subfolder

1. **Upload files** to your subfolder:
   ```
   /public_html/connexion/
   ```

2. **Access via browser**:
   ```
   https://yourdomain.com/connexion/public/
   ```

3. **Automatic setup** will run and detect the subfolder

### Option 2: Document Root in Subfolder

If you want cleaner URLs without `/public/`:

1. **Upload to subfolder**:
   ```
   /home/user/connexion/
   ```

2. **Point web server to public folder**:

   **Apache (.htaccess in subfolder root):**
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

   **Nginx (in server config):**
   ```nginx
   location /connexion {
       alias /home/user/connexion/public;
       try_files $uri $uri/ @connexion;
       
       location ~ \.php$ {
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $request_filename;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
       }
   }
   
   location @connexion {
       rewrite /connexion/(.*)$ /connexion/index.php?/$1 last;
   }
   ```

3. **Access via browser**:
   ```
   https://yourdomain.com/connexion/
   ```

## Fixing Common Issues

### Issue: "Page not found" or 404 errors

**Cause:** Web server not configured for rewrite rules in subfolder.

**Solution for Apache:**

Create/edit `.htaccess` in the `public` folder:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    
    # Get the base path
    RewriteBase /connexion/public/

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Key change:** `RewriteBase /connexion/public/` (adjust to your path)

### Issue: Composer HOME error

**Error message:**
```
The HOME or COMPOSER_HOME environment variable must be set for composer to run correctly
```

**Fixed in latest version!** The setup script now automatically:
- Creates `storage/composer` directory
- Sets `COMPOSER_HOME` and `HOME` environment variables
- Uses this directory for composer cache

**Manual fix if needed:**
```bash
export COMPOSER_HOME=/path/to/connexion/storage/composer
export HOME=/path/to/connexion/storage/composer
composer install
```

### Issue: Assets not loading (CSS/JS 404)

**Cause:** Asset URLs not accounting for subfolder.

**Solution:** Update `.env` after installation:
```env
APP_URL=https://yourdomain.com/connexion
ASSET_URL=https://yourdomain.com/connexion
```

Then clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Redirects going to wrong location

**Cause:** Base URL detection issue.

**Check these files are updated:**
- `public/index.php` - Has subfolder detection
- `public/setup.php` - Has base URL detection
- `.env` - Has correct APP_URL

**Test base URL detection:**
```php
// Add to setup.php temporarily for debugging
echo "Detected base URL: " . $baseUrl . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
```

## Web Server Configuration

### Apache with Virtual Host

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html
    
    Alias /connexion /path/to/connexion/public
    
    <Directory /path/to/connexion/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html;
    
    location /connexion {
        alias /path/to/connexion/public;
        try_files $uri $uri/ @connexion;
        
        index index.php index.html;
        
        location ~ \.php$ {
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }
    
    location @connexion {
        rewrite /connexion/(.*)$ /connexion/index.php?/$1 last;
    }
}
```

### XAMPP/WAMP (Windows)

1. Place files in: `C:\xampp\htdocs\connexion\`

2. Access via: `http://localhost/connexion/public/`

3. Edit `public\.htaccess`:
   ```apache
   RewriteBase /connexion/public/
   ```

### MAMP (macOS)

1. Place files in: `/Applications/MAMP/htdocs/connexion/`

2. Access via: `http://localhost:8888/connexion/public/`

3. Update `.htaccess` with port if needed

## Environment Configuration

After installation, verify your `.env`:

```env
APP_NAME=Connexion
APP_URL=http://localhost/connexion

# For production with subdomain
# APP_URL=https://yourdomain.com/connexion

# If using asset CDN or different asset path
# ASSET_URL=https://cdn.yourdomain.com/connexion
```

## Troubleshooting Checklist

- [ ] Web server rewrite module enabled
  - Apache: `a2enmod rewrite`
  - Nginx: Built-in, check config
  
- [ ] `.htaccess` has correct `RewriteBase`

- [ ] `APP_URL` in `.env` matches actual URL

- [ ] Public folder is accessible

- [ ] Permissions are correct (775 for directories)

- [ ] PHP version is 8.2+ (`php -v`)

- [ ] All PHP extensions installed

- [ ] Composer dependencies installed

- [ ] `storage/setup_complete` exists

## Production Deployment

For production in a subfolder:

1. **Set correct permissions:**
   ```bash
   find /path/to/connexion -type d -exec chmod 755 {} \;
   find /path/to/connexion -type f -exec chmod 644 {} \;
   chmod -R 775 storage bootstrap/cache
   ```

2. **Update .env for production:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com/connexion
   ```

3. **Optimize for production:**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Remove setup.php:**
   ```bash
   rm public/setup.php
   ```

## Testing Your Installation

Test these URLs work:

- `https://yourdomain.com/connexion/` → Redirects to install or login
- `https://yourdomain.com/connexion/install` → Shows installer (if not complete)
- `https://yourdomain.com/connexion/admin` → Shows admin panel (after install)

## Moving from Subfolder to Root

If you later want to move from subfolder to root:

1. Move files to root

2. Update `.env`:
   ```env
   APP_URL=https://yourdomain.com
   ```

3. Update `.htaccess`:
   ```apache
   RewriteBase /
   ```

4. Clear caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

## Support

If you encounter issues with subfolder installation:

1. Check `storage/logs/laravel.log`
2. Verify web server error logs
3. Test with absolute URLs first
4. Ensure rewrite rules are working
5. Try manual composer install if automation fails