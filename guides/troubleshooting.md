# Troubleshooting Guide

Common issues and solutions for the Upload and Shorten Advanced plugin.

## Common Issues

### File Uploads Not Working

**Symptoms:**
- Upload button doesn't work
- Files fail to upload with no error
- Progress bar stalls

**Solutions:**

1. **Check Directory Permissions**

```bash
# Verify upload directory is writable
ls -la /path/to/yourls/user/plugins/YOURLS-Upload-and-Shorten-Advanced/uploads/
# Should show 755 or 777 permissions

# Fix permissions if needed
chmod -R 755 YOURLS-Upload-and-Shorten-Advanced/
chmod 777 YOURLS-Upload-and-Shorten-Advanced/uploads/
```

2. **Verify Upload Directory Exists**

```bash
# Create if missing
mkdir -p /path/to/yourls/user/plugins/YOURLS-Upload-and-Shorten-Advanced/uploads/
chown www-data:www-data uploads/
```

3. **Check PHP Upload Limits**

Edit `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M
```

Restart web server after changes:

```bash
systemctl restart apache2    # Apache
systemctl restart php-fpm    # PHP-FPM
systemctl restart lsws       # LiteSpeed
```

4. **Verify Database Tables**

```sql
-- Check if tables exist
SHOW TABLES LIKE 'yourls_upload%';

-- Should show:
-- yourls_upload_files
-- yourls_upload_settings
```

If tables are missing, deactivate and reactivate the plugin.

### Short URLs Not Redirecting

**Symptoms:**
- Short URL returns 404 error
- URL doesn't redirect to file
- "File not found" message

**Solutions:**

1. **Check File Location**

Verify file exists in upload directory:

```bash
ls -la /path/to/uploads/
```

2. **Verify Upload Directory is Web-Accessible**

Test direct access:

```
https://yourls.example.com/user/plugins/YOURLS-Upload-and-Shorten-Advanced/uploads/test.txt
```

If this doesn't work, the directory isn't web-accessible.

3. **Check .htaccess Rules**

Verify `.htaccess` exists in uploads directory:

```apache
# Allow file access
<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx|txt|zip)$">
    Allow from all
</FilesMatch>

# Prevent PHP execution
<Files *.php>
    deny from all
</Files>
```

4. **Verify YOURLS URL Rewriting**

Ensure YOURLS rewrite rules are working:

```bash
# Test with a regular YOURLS short URL first
curl -I https://yourls.example.com/test
```

If regular YOURLS URLs don't work, fix YOURLS configuration first.

### Frontend Uploads Not Showing

**Symptoms:**
- Upload form doesn't appear on homepage
- Only admin can see upload option
- Public users get "access denied"

**Solutions:**

1. **Check Frontend Upload Settings**

Go to **Admin Panel → Upload Settings**:
- Verify "Enable Frontend Uploads" is set to "Yes"
- Check frontend file size and type restrictions
- Save settings

2. **Verify Theme Compatibility**

Test with default YOURLS theme:

```php
// In user/config.php, temporarily change theme
define('YOURLS_THEME', 'default');
```

If it works with default theme, your custom theme needs updates.

3. **Check for JavaScript Errors**

Open browser console (F12) and look for errors. Common issues:
- jQuery not loaded
- Script conflicts
- CORS issues

4. **Verify Form Hook**

Check that your theme includes the upload hook:

```php
// Should be in your theme's index.php
yourls_do_action('upload_frontend_form');
```

### Database Errors

**Symptoms:**
- "Table doesn't exist" errors
- "Cannot insert into table" errors
- Plugin activation fails

**Solutions:**

1. **Verify Database Permissions**

```sql
-- Check user permissions
SHOW GRANTS FOR 'yourls_user'@'localhost';

-- Should include:
-- CREATE, INSERT, UPDATE, DELETE, SELECT
```

2. **Manually Create Tables**

If activation fails, create tables manually:

```sql
-- Run the SQL from sql/install.sql
SOURCE /path/to/YOURLS-Upload-and-Shorten-Advanced/sql/install.sql;
```

3. **Check Database Connection**

Verify YOURLS can connect:

```php
// In a test file
require_once('/path/to/yourls/includes/load-yourls.php');
$db = yourls_get_db();
print_r($db);
```

4. **Run Activation Function Again**

```php
// Deactivate plugin
// Then reactivate to trigger table creation
```

### Permission Errors

**Symptoms:**
- "Permission denied" when uploading
- Cannot create directories
- Cannot write files

**Solutions:**

1. **Fix File Ownership**

```bash
# For standard web servers
chown -R www-data:www-data YOURLS-Upload-and-Shorten-Advanced/

# For CyberPanel
chown -R yourls_user:yourls_user YOURLS-Upload-and-Shorten-Advanced/
chown yourls_user:nobody YOURLS-Upload-and-Shorten-Advanced/
```

2. **Fix Directory Permissions**

```bash
# Directories
find YOURLS-Upload-and-Shorten-Advanced/ -type d -exec chmod 755 {} \;

# Files
find YOURLS-Upload-and-Shorten-Advanced/ -type f -exec chmod 644 {} \;

# Uploads directory
chmod 777 YOURLS-Upload-and-Shorten-Advanced/uploads/
```

3. **Check SELinux (if applicable)**

```bash
# Check if SELinux is enabled
getenforce

# If enforcing, add context
chcon -R -t httpd_sys_rw_content_t uploads/

# Or disable SELinux (not recommended for production)
setenforce 0
```

4. **Check AppArmor (if applicable)**

```bash
# Check AppArmor status
aa-status

# If blocking, adjust profile or disable for web server
```

### Rate Limiting Issues

**Symptoms:**
- "Too many uploads" error
- Cannot upload despite waiting
- Rate limit counter wrong

**Solutions:**

1. **Clear Rate Limit Data**

```sql
-- Clear rate limit table
DELETE FROM yourls_upload_rate_limits WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

2. **Adjust Rate Limits**

In `user/config.php`:

```php
define('UPLOAD_RATE_LIMIT', 20);  // Increase limit
define('UPLOAD_RATE_WINDOW', 3600);  // Adjust window
```

3. **Whitelist Your IP**

```php
define('UPLOAD_RATE_LIMIT_WHITELIST', [
    'YOUR.IP.ADDRESS.HERE',
]);
```

4. **Disable Rate Limiting (temporarily)**

```php
define('UPLOAD_RATE_LIMIT_ENABLED', false);
```

## Debug Mode

Enable debug mode to get detailed error information.

### Enable Plugin Debug Mode

Add to `user/config.php`:

```php
define('YOURLS_DEBUG', true);
define('UPLOAD_DEBUG', true);
```

### Check Log Files

View plugin logs:

```bash
tail -f /path/to/yourls/user/logs/upload-plugin.log
```

View PHP errors:

```bash
tail -f /var/log/php/error.log
# Or
tail -f /var/log/apache2/error.log
# Or
tail -f /var/log/nginx/error.log
```

### Increase PHP Error Reporting

```php
// In user/config.php (temporarily)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Test Upload Manually

Create a test script:

```php
<?php
// test-upload.php
require_once('includes/load-yourls.php');

$file = [
    'name' => 'test.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/test.txt',
    'size' => 100
];

$result = yourls_upload_file($file);
print_r($result);
```

## Performance Issues

### Slow Uploads

**Solutions:**

1. **Increase PHP Limits**

```ini
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
```

2. **Optimize Database**

```sql
OPTIMIZE TABLE yourls_upload_files;
OPTIMIZE TABLE yourls_url;
```

3. **Enable Caching**

```php
define('UPLOAD_ENABLE_CACHE', true);
```

### High Server Load

**Solutions:**

1. **Enable Rate Limiting**
2. **Set lower file size limits**
3. **Implement CDN for file delivery**
4. **Use separate storage server**

## Getting Help

If you're still experiencing issues:

### Before Asking for Help

Please gather this information:

1. YOURLS version
2. PHP version
3. Web server (Apache/Nginx/LiteSpeed)
4. Error messages from logs
5. Steps to reproduce the issue
6. What you've already tried

### Support Channels

- **Discord:** [Join our Discord Server](https://discord.gg/nx9Kzrk)
- **GitHub Issues:** [Report a bug](https://github.com/master3395/YOURLS-Upload-and-Shorten-Advanced/issues)
- **Email:** [info@newstargeted.com](mailto:info@newstargeted.com)

### Useful Commands for Support

Gather system information:

```bash
# PHP version
php -v

# Web server version
apache2 -v   # or nginx -v, or /usr/local/lsws/bin/lshttpd -v

# Permissions
ls -la /path/to/YOURLS-Upload-and-Shorten-Advanced/

# Disk space
df -h

# PHP modules
php -m

# Recent errors
tail -50 /var/log/php/error.log
```

## Next Steps

- [Return to configuration guide](configuration.md)
- [Learn about advanced features](advanced-configuration.md)
- [Back to usage guide](usage.md)

## Back to Guides

[← Back to Documentation Index](README.md)

