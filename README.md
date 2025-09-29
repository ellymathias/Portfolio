# Portfolio Backend Implementation

This is a complete PHP backend implementation for the Elia Mathias Portfolio website, designed to work with XAMPP and MySQL.

## Features

- **Contact Form Handling**: Secure form processing with validation and file uploads
- **Analytics Tracking**: Comprehensive user behavior tracking
- **Admin Dashboard**: View and manage contact submissions
- **Security Features**: Rate limiting, input sanitization, and security logging
- **File Upload**: Secure resume upload handling
- **Email Notifications**: Automatic email notifications for new submissions

## Setup Instructions

### 1. Database Setup

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database called `portfolio_db`
4. Import the SQL schema from `database/schema.sql`

### 2. Configuration

1. Edit `config/database.php` and update the following:
   - Database credentials (DB_USER, DB_PASS)
   - Email settings for notifications
   - Application URL
   - Admin email address

### 3. File Permissions

Ensure the following directories are writable:
- `uploads/` (for resume uploads)
- `config/` (for configuration files)

### 4. Run Setup Script

Visit `http://localhost/Portfolio/setup.php` to:
- Test database connection
- Check table creation
- Verify file permissions
- Test API endpoints

## File Structure

```
Portfolio/
├── api/
│   ├── contact.php          # Contact form API
│   ├── analytics.php        # Analytics tracking API
│   └── track.php           # Page view tracking API
├── admin/
│   ├── index.php           # Admin dashboard
│   └── view.php            # View individual submissions
├── config/
│   └── database.php        # Database configuration
├── database/
│   └── schema.sql          # Database schema
├── uploads/                # File upload directory
├── setup.php              # Setup verification script
└── README.md              # This file
```

## API Endpoints

### Contact Form API (`api/contact.php`)
- **Method**: POST
- **Purpose**: Handle contact form submissions
- **Features**: Validation, file upload, email notifications

### Analytics API (`api/analytics.php`)
- **Method**: POST/GET
- **Purpose**: Track user interactions and retrieve analytics data
- **Features**: Event tracking, data retrieval for admin dashboard

### Page Tracking API (`api/track.php`)
- **Method**: POST
- **Purpose**: Track page views and user sessions
- **Features**: Session management, page view analytics

## Admin Dashboard

Access the admin dashboard at: `http://localhost/Portfolio/admin/index.php`

**Default Login**: 
- Password: `admin123` (change this immediately!)

**Features**:
- View contact form submissions
- Update submission status
- View analytics and statistics
- Download resume attachments
- Reply to submissions via email

## Security Features

1. **Rate Limiting**: Prevents spam and abuse
2. **Input Sanitization**: All user input is sanitized
3. **File Upload Validation**: Secure file type and size validation
4. **Security Logging**: All security events are logged
5. **CSRF Protection**: Cross-site request forgery protection
6. **SQL Injection Prevention**: Prepared statements used throughout

## Database Tables

- `contact_submissions`: Stores contact form submissions
- `analytics_events`: Tracks user interactions and events
- `page_views`: Records page view statistics
- `security_logs`: Logs security-related events
- `newsletter_subscriptions`: Newsletter subscription management

## Email Configuration

To enable email notifications:

1. Update SMTP settings in `config/database.php`
2. For Gmail, use an App Password instead of your regular password
3. Test email functionality through the contact form

## Production Deployment

Before deploying to production:

1. Change all default passwords
2. Enable HTTPS
3. Set `DEBUG_MODE` to `false` in `config/database.php`
4. Configure proper SMTP settings
5. Set up regular database backups
6. Monitor security logs regularly

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check XAMPP MySQL is running
   - Verify database credentials in `config/database.php`
   - Ensure database `portfolio_db` exists

2. **File Upload Not Working**
   - Check `uploads/` directory permissions
   - Verify `MAX_FILE_SIZE` setting
   - Check PHP upload limits in php.ini

3. **Email Notifications Not Sending**
   - Verify SMTP settings
   - Check firewall settings
   - Use App Password for Gmail

4. **Admin Dashboard Access Issues**
   - Check session configuration
   - Verify file permissions
   - Clear browser cache

### Logs

Check the following for debugging:
- PHP error logs in XAMPP
- Security logs in `security_logs` table
- Browser console for JavaScript errors

## Support

For issues or questions:
- Check the setup script output
- Review security logs
- Verify file permissions
- Test API endpoints individually

## License

This backend implementation is part of the Elia Mathias Portfolio project.
