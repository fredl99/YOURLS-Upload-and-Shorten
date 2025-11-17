# Installation Guide

Complete installation instructions for the Upload and Shorten Advanced plugin.

## Installation Methods

### Option 1: Using Download Plugin (Recommended)

If you have the [Download Plugin](https://github.com/krissss/yourls-download-plugin) installed:

1. Go to your YOURLS admin panel
2. Navigate to the Download Plugin page
3. Paste this URL: `https://github.com/master3395/YOURLS-Upload-and-Shorten-Advanced`
4. Click "Download"
5. The plugin will be automatically installed

Then proceed to [Set Permissions](#2-set-permissions) for next steps.

### Option 2: Manual Installation

```bash
# Navigate to your YOURLS plugins directory
cd /path/to/yourls/user/plugins/

# Clone the plugin
git clone https://github.com/master3395/YOURLS-Upload-and-Shorten-Advanced.git
```

## 2. Set Permissions

After installation, set proper permissions:

```bash
# Set proper ownership and permissions
chown -R yourls_user:yourls_group YOURLS-Upload-and-Shorten-Advanced/
chmod -R 755 YOURLS-Upload-and-Shorten-Advanced/
chmod 777 YOURLS-Upload-and-Shorten-Advanced/uploads/
```

Replace `yourls_user` and `yourls_group` with your actual web server user and group (e.g., `www-data`, `apache`, or your specific user).

## 3. Activate the Plugin

1. Go to your YOURLS admin panel: `https://yourls.example.com/admin/plugins.php`
2. Find "Upload & Shorten" in the plugin list
3. Click "Activate"
4. The plugin will automatically create necessary database tables

## 4. Configure Settings

After activation, configure your plugin:

1. Navigate to **Upload Settings** in the admin panel
2. Configure your preferred settings:
   - Set maximum file size
   - Configure allowed file types
   - Choose storage location
   - Enable/disable frontend uploads
   - Configure file expiration settings
3. Save your configuration

## Verification

After installation, verify everything is working:

1. Check that the plugin appears as "Active" in the plugins list
2. Verify the "Upload Settings" page is accessible
3. Try uploading a test file in the admin panel
4. Confirm the short URL works and redirects to your file
5. Check that the uploads directory is writable

## Server Requirements

- **YOURLS:** 1.7 or higher
- **PHP:** 7.4 - 8.6
- **Web Server:** Apache, Nginx, OpenLiteSpeed, or LiteSpeed Enterprise
- **Database:** MySQL 5.6+ or MariaDB 10.0+
- **PHP Extensions:** 
  - fileinfo
  - mysqli
  - json
  - mbstring

## Directory Structure

After installation, your plugin directory should look like this:

```
YOURLS-Upload-and-Shorten-Advanced/
├── plugin.php              # Main plugin file
├── README.md              # Main documentation
├── modules/               # Plugin modules
├── l10n/                 # Language files
├── images/               # Documentation images
├── changelogs/           # Version history
├── guides/               # Documentation guides
├── sql/                  # Database schemas
└── uploads/              # File storage (created on first use)
```

## Troubleshooting Installation

### Plugin Won't Activate

- Check that you're running YOURLS 1.7 or higher
- Verify PHP version is 7.4 or higher
- Check database connection and permissions
- Review server error logs for details

### Database Errors

- Ensure database user has CREATE and INSERT permissions
- Check database connection settings in YOURLS config
- Try deactivating and reactivating the plugin

### Permission Issues

- Ensure web server has write access to the plugin directory
- Check that the uploads directory is writable (777 or 755 with proper ownership)
- Verify SELinux or AppArmor settings if applicable

## Next Steps

- [Configure the plugin](configuration.md)
- [Learn how to use it](usage.md)
- [Set up advanced features](advanced-configuration.md)

## Back to Guides

[← Back to Documentation Index](README.md)

