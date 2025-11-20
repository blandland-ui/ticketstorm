# TicketStorm - Tech Support Ticket System

A complete ticket management system with static HTML frontend and PHP backend for handling tech support requests.

## Features

- ✅ **User Registration & Authentication** - Secure user accounts with password hashing
- ✅ **Ticket Submission** - Users can submit detailed support tickets with priority levels
- ✅ **Ticket Tracking** - Monitor ticket status from submission to resolution
- ✅ **Admin Panel** - Comprehensive dashboard for managing all tickets and users
- ✅ **Comments System** - Two-way communication between users and support staff
- ✅ **Priority Levels** - Low, Medium, High, and Critical priority classification
- ✅ **Status Management** - Open, In Progress, Resolved, and Closed states
- ✅ **Responsive Design** - Works on desktop, tablet, and mobile devices

## Quick Start

### Default Admin Login
- **Username:** `admin`
- **Password:** `admin123`
- ⚠️ **Change this password immediately after first login!**

### User Flow
1. Visit `index.html`
2. Register a new account
3. Login with credentials
4. Create support tickets
5. Track ticket progress
6. Communicate via comments

### Admin Flow
1. Login with admin credentials
2. View dashboard statistics
3. Manage all tickets
4. Update ticket status and priority
5. Respond to user tickets

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Authentication:** Session-based with password hashing

## Project Structure

```
ticketstorm/
├── index.html              # Landing page
├── login.html              # Login page
├── register.html           # Registration page
├── dashboard.html          # User dashboard
├── new-ticket.html         # Create ticket
├── admin.html              # Admin panel
├── api/
│   ├── auth.php           # Authentication endpoints
│   └── tickets.php        # Ticket management endpoints
├── config/
│   └── database.php       # Database configuration
├── assets/
│   └── css/
│       └── style.css      # Complete styling
├── database/
│   └── setup.sql          # Database schema
├── DEPLOYMENT.md          # Deployment instructions
└── README.md             # This file
```

## Database Schema

### Users Database (blacoksf_ticket_storm_users)
- **users** - User accounts with authentication data

### Tickets Database (blacoksf_ticket_storm_tickets)
- **tickets** - Support ticket records
- **ticket_comments** - Comments and responses

## API Endpoints

### Authentication API (`api/auth.php`)
- `POST ?action=register` - Create new user account
- `POST ?action=login` - User login
- `POST ?action=logout` - User logout
- `GET ?action=check` - Check authentication status

### Tickets API (`api/tickets.php`)
- `POST ?action=create` - Create new ticket
- `GET ?action=list` - List tickets (filtered by user or all for admin)
- `GET ?action=get&ticket_id=X` - Get ticket details
- `POST ?action=update` - Update ticket (admin only)
- `POST ?action=comment` - Add comment to ticket
- `GET ?action=stats` - Get dashboard statistics (admin only)

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- CSRF protection ready
- Role-based access control (user/admin)

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for complete deployment instructions.

**Quick deployment summary:**
1. Upload files to server
2. Import `database/setup.sql`
3. Configure `config/database.php`
4. Set file permissions
5. Access via browser

## Server Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (optional)
- SSL certificate (recommended)

## Configuration

Edit `config/database.php` to match your server:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_USERS', 'your_users_database');
define('DB_TICKETS', 'your_tickets_database');
```

## Customization

### Change Color Scheme
Edit CSS variables in `assets/css/style.css`:

```css
:root {
    --primary-color: #4F46E5;
    --secondary-color: #10B981;
    --danger-color: #EF4444;
    /* ... more colors ... */
}
```

### Add Email Notifications
Integrate PHP mailer in `api/tickets.php` for:
- New ticket notifications
- Status change alerts
- Admin response notifications

### Additional Features
- File attachments for tickets
- Ticket assignment to specific admins
- Ticket categories management
- Search functionality
- Export reports to PDF/CSV

## Troubleshooting

### Can't login?
- Check database credentials in `config/database.php`
- Verify database tables exist
- Check PHP error logs

### Tickets not showing?
- Verify API endpoints are accessible
- Check browser console for JavaScript errors
- Confirm user is logged in

### Admin panel not accessible?
- Ensure user has admin privileges in database
- Check `is_admin` field in users table

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

This project is provided as-is for personal and commercial use.

## Credits

Built with modern web technologies and best practices for security and usability.

---

**Server Details:**
- Domain: blacksitedb.com
- Entry Point: index.html
- Admin Panel: admin.html
