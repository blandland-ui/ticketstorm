# TicketStorm Deployment Guide

## Prerequisites
- Server with PHP 7.4 or higher
- MySQL database
- SSH/FTP access to your server

## Server Information
- **Domain**: blacksitedb.com
- **Server IP**: 198.54.125.130
- **Root Directory**: /home/blacoksf/public_html
- **FTP**: ftp.blacksitedb.com
- **User**: blacoksf
- **Databases**:
  - blacoksf_ticket_storm_users
  - blacoksf_ticket_storm_tickets
- **Database User**: blacoksf_admin

## Deployment Steps

### 1. Upload Files to Server

#### Option A: Using FTP
1. Connect to FTP: `ftp.blacksitedb.com`
2. Upload all files from the `ticketstorm` folder to `/home/blacoksf/public_html/`
3. Ensure the following structure:
   ```
   public_html/
   ├── index.html
   ├── login.html
   ├── register.html
   ├── dashboard.html
   ├── new-ticket.html
   ├── admin.html
   ├── api/
   │   ├── auth.php
   │   └── tickets.php
   ├── config/
   │   └── database.php
   ├── assets/
   │   └── css/
   │       └── style.css
   └── database/
       └── setup.sql
   ```

#### Option B: Using SSH
```bash
# Connect via SSH
ssh blacoksf@198.54.125.130

# Navigate to web root
cd /home/blacoksf/public_html

# Upload files using SFTP or rsync
```

### 2. Set Up Databases

#### Using phpMyAdmin (Recommended)
1. Log into your hosting control panel (cPanel or similar)
2. Open phpMyAdmin
3. Import the database structure:
   - Select database `blacoksf_ticket_storm_users`
   - Click "Import"
   - Upload `database/setup.sql`
   - Click "Go"

#### Using SSH
```bash
# Connect to MySQL
mysql -u blacoksf_admin -p

# Run the SQL file
source /home/blacoksf/public_html/database/setup.sql
```

### 3. Configure Database Connection

Edit `config/database.php` to ensure correct credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'blacoksf_admin');
define('DB_PASS', 'The  hater#');  // Update if needed
define('DB_USERS', 'blacoksf_ticket_storm_users');
define('DB_TICKETS', 'blacoksf_ticket_storm_tickets');
```

### 4. Set File Permissions

Via SSH:
```bash
# Make sure PHP files are readable
chmod 644 config/database.php
chmod 644 api/*.php

# Ensure directories are accessible
chmod 755 api/
chmod 755 config/
chmod 755 assets/
```

### 5. Test the Installation

1. Open your browser and navigate to: `http://blacksitedb.com/index.html`
2. Click "Register" and create a test account
3. Login with your credentials
4. Try creating a test ticket

### 6. Access Admin Panel

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

⚠️ **IMPORTANT**: Change the admin password immediately!

To change admin password:
1. Log in as admin
2. Or update directly in database:
```sql
USE blacoksf_ticket_storm_users;
UPDATE users SET password_hash = '$2y$10$YOUR_NEW_HASH' WHERE username = 'admin';
```

To generate a new password hash in PHP:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

## Security Recommendations

### 1. Enable HTTPS
Configure SSL certificate through your hosting provider for secure connections.

### 2. Update Session Security
In `config/database.php`, change:
```php
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
```

### 3. Protect Sensitive Files
Create `.htaccess` file in root directory:
```apache
# Prevent directory listing
Options -Indexes

# Protect config files
<FilesMatch "^(database\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect SQL files
<Files ~ "\.sql$">
    Order allow,deny
    Deny from all
</Files>
```

### 4. Database Security
- Change database password from default
- Use strong passwords for all accounts
- Limit database user permissions to only necessary operations

### 5. PHP Configuration
Ensure these settings in `php.ini`:
```ini
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

## Troubleshooting

### Database Connection Errors
- Verify database credentials in `config/database.php`
- Check if MySQL service is running
- Ensure database user has proper permissions

### Session Issues
- Check PHP session directory permissions
- Verify `session_start()` is called before any output

### 404 Errors
- Verify files are in correct directory
- Check file permissions (644 for files, 755 for directories)
- Ensure index.html is in web root

### API Errors
- Check PHP error logs
- Verify `api/` folder permissions
- Test API endpoints directly: `http://blacksitedb.com/api/auth.php?action=check`

## Maintenance

### Regular Backups
Backup these regularly:
1. Database (both `blacoksf_ticket_storm_users` and `blacoksf_ticket_storm_tickets`)
2. All uploaded files
3. Configuration files

### Database Backup Commands
```bash
# Backup users database
mysqldump -u blacoksf_admin -p blacoksf_ticket_storm_users > backup_users.sql

# Backup tickets database
mysqldump -u blacoksf_admin -p blacoksf_ticket_storm_tickets > backup_tickets.sql
```

### Update Monitoring
- Monitor error logs regularly
- Check for failed login attempts
- Review ticket statistics for unusual activity

## Optional Enhancements

### Email Notifications
Add SMTP configuration for email notifications when:
- New ticket is submitted
- Ticket status changes
- Admin responds to ticket

### Custom Domain
Configure your domain to point to your server:
1. Update DNS A record to point to 198.54.125.130
2. Configure virtual host on server
3. Install SSL certificate

### Automated Backups
Set up cron job for automated backups:
```bash
0 2 * * * /path/to/backup_script.sh
```

## Support

For issues or questions:
1. Check error logs: `/home/blacoksf/public_html/error_log`
2. Verify PHP version: `php -v`
3. Test database connection through phpMyAdmin
4. Review server requirements with hosting provider

## File Structure Reference

```
ticketstorm/
├── index.html              # Landing page (entry point)
├── login.html              # User login page
├── register.html           # User registration page
├── dashboard.html          # User dashboard
├── new-ticket.html         # Ticket creation form
├── admin.html              # Admin panel
├── api/
│   ├── auth.php           # Authentication API
│   └── tickets.php        # Ticket management API
├── config/
│   └── database.php       # Database configuration
├── assets/
│   └── css/
│       └── style.css      # Complete styling
├── database/
│   └── setup.sql          # Database schema
└── DEPLOYMENT.md          # This file
```

## Post-Deployment Checklist

- [ ] Files uploaded to server
- [ ] Database tables created
- [ ] Database credentials configured
- [ ] File permissions set correctly
- [ ] Default admin password changed
- [ ] Test user registration works
- [ ] Test ticket creation works
- [ ] Admin panel accessible
- [ ] SSL/HTTPS enabled (if available)
- [ ] Backups scheduled
- [ ] Error logging configured

---

**Your TicketStorm installation is complete! Access it at:** http://blacksitedb.com/index.html
