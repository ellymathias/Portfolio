# Portfolio Hosting Guide

## Pre-Deployment Checklist

### 1. File Preparation
- [ ] Remove or comment out Firebase config in index.html
- [ ] Update Google Analytics ID (replace GA_MEASUREMENT_ID)
- [ ] Update domain URLs in config/database.php
- [ ] Test locally with XAMPP

### 2. Database Configuration
Update `config/database.php` with hosting provider details:

```php
// Example for InfinityFree
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'ifXXX_portfolio_db');
define('DB_USER', 'ifXXX_username');
define('DB_PASS', 'your_password');
define('APP_URL', 'https://eliamathias.infinityfreeapp.com');
```

### 3. File Upload Methods

#### Method 1: File Manager (Easiest)
1. Login to hosting control panel
2. Open File Manager
3. Navigate to public_html or htdocs
4. Upload all files via web interface

#### Method 2: FTP (Recommended)
1. Download FTP client (FileZilla)
2. Use FTP credentials from hosting panel
3. Upload files to public_html directory

### 4. Database Setup
1. Create MySQL database in control panel
2. Import database/schema.sql
3. Update database credentials in config/database.php

### 5. Testing
1. Visit your website URL
2. Test contact form
3. Check admin dashboard
4. Verify file uploads work

## Hosting Provider Specific Instructions

### InfinityFree
- Control Panel: https://infinityfree.net/control-panel
- File Manager: Available in control panel
- MySQL: Create database in "MySQL Databases" section
- Custom Domain: Add in "Addon Domains" section

### 000Webhost
- Control Panel: https://files.000webhost.com
- File Manager: Built-in file manager
- MySQL: Available in control panel
- Custom Domain: Add in "Domain" section

## Security Considerations
- Change admin password immediately
- Use HTTPS (most free hosts provide SSL)
- Regular backups of database
- Monitor security logs

## Troubleshooting
- Check file permissions (755 for folders, 644 for files)
- Verify database connection
- Check error logs in hosting panel
- Test API endpoints individually
