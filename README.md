# Weekly Savings Plan App

A web application to track weekly savings payments for groups of users with multi-tenancy support.

## Description

The Weekly Savings Plan App helps groups of users track their savings goals on a weekly basis. Each week has a savings goal that increases gradually (Week 1 = $1,000, Week 2 = $2,000, etc.). The app supports multiple independent groups (bags), each with their own users, payments, activities, and expenses.

## Default Login

- **Username**: admin
- **Password**: abcd1234

You will be prompted to change your password on first login.

## Features

- **Multi-Group Support**: Independent groups (bags) with isolated data per group
- **Weekly Savings Plan**: Visual calendar view showing savings progress for each week
- **Payment Tracking**: Record payments and track which weeks are paid, partially paid, or unpaid
- **Multi-User Support**: Multiple users per group, each with their own savings multiplier
- **Activities & Expenses**: Extra charges or expenses tracked per activity
- **Role-Based Access**: Superadmin, Admin, Normal, and Disabled roles
- **User Management**: Create, edit, and manage users with unique usernames per group
- **Group Management**: Create, edit, disable, and truncate groups with data export
- **Activity Logs**: Track all actions with timestamp, action type, and details
- **HTTPS Support**: Automatic SSL certificates via Caddy reverse proxy
- **Bilingual**: English and Spanish
- **Responsive Design**: Works on desktop and mobile devices
- **Profile Pictures**: Users and groups can have profile pictures with thumbnails

## Roles

| Role | Value | Access |
|------|-------|--------|
| Disabled | 0 | Cannot login |
| Normal | 1 | Own payments only |
| Admin | 2 | Full access except group CRUD |
| Superadmin | 3 | Everything + group management |

## Usage

### Groups (Bags)
- Each group is an independent savings community
- Users belong to one group (except superadmins who can access multiple)
- Superadmins can create, edit, disable, and truncate groups
- Truncating creates a SQL backup before deleting all data

### Weekly Plan
- View savings progress for each week of the year
- Filter by user or view all users combined
- See color-coded status for each week

### Payments
- Record new payments with user, amount, and payment method
- Filter payments by user, method, or month
- Edit or delete existing payments (admin only)
- Verify payments to lock them from further edits

### User Management (Admin Only)
- Create new users with unique usernames (per group)
- Set savings multipliers for each user
- Reset user passwords
- Enable/disable user accounts

### Activities & Expenses
- Create extra charges or expenses
- Track activity payments separately from weekly goals
- Confirm expenses to lock them

### Activity Logs (Admin Only)
- View a log of all actions in the current group
- Filter by date range, user, or action type
- Track who performed each action vs. who owns the record

## Requirements

- Docker and Docker Compose
- PHP 8.2 (included in Docker container)
- MySQL 8.0 (included in Docker container)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/delacuestajs/weekly-savings-plan-app.git
   cd weekly-savings-plan-app
   ```

2. Copy the environment file and configure:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your settings (database credentials, domain, port, etc.)

3. Start the application using Docker Compose:
   ```bash
   docker compose up -d
   ```

4. Access the application at `http://localhost:9283` (or your configured port)

For detailed deployment instructions (remote server, HTTPS, Docker context), see [DEPLOYMENT.md](DEPLOYMENT.md).

## Project Structure

```
weekly-savings-plan-app/
├── config/               # Configuration files
│   ├── database.php      # Database connection (reads env vars)
│   └── config.php        # App version and build info
├── controllers/          # Application controllers
│   ├── ActivityController.php
│   ├── Auth.php
│   ├── BagController.php
│   ├── DashboardController.php
│   ├── ExpenseController.php
│   ├── LogController.php
│   ├── SavingController.php
│   └── UserController.php
├── database/             # Database migrations and schema
│   ├── schema.sql
│   └── migration_*.sql
├── lang/                 # Language files
│   ├── en.php            # English translations
│   └── es.php            # Spanish translations
├── models/               # Data models
│   ├── Activity.php
│   ├── ActivityLog.php
│   ├── Bag.php
│   ├── Expense.php
│   ├── Saving.php
│   ├── User.php
│   └── WeeklySaving.php
├── views/                # View templates (list views only, CRUD is modal)
│   ├── activities/
│   │   └── list.php
│   ├── bags/
│   │   └── list.php
│   ├── logs/
│   │   └── list.php
│   ├── users/
│   │   └── list.php
│   ├── dashboard.php
│   ├── footer.php
│   ├── header.php        # Global modals + navigation
│   ├── list.php          # Payments list
│   ├── login.php
│   └── weekly.php
├── caddy/                # Caddy reverse proxy config
│   └── Caddyfile
├── uploads/              # User/bag uploads
│   ├── bags/             # Bag pictures
│   └── dumps/            # Truncate backup files
├── .env.example          # Example environment variables
├── .env                  # Actual environment variables (gitignored)
├── docker-compose.yml
├── Dockerfile
├── index.php             # Main entry point + router
└── locale.php            # Locale and timezone management
```

## Database

MySQL 8.0 with the following main tables:
- `users` - User accounts and settings
- `savings` - Payment records
- `activities` - Extra charges or expenses
- `expenses` - Individual expenses per activity
- `activity_logs` - Action audit trail
- `bags` - Groups/organizations
- `bag_user` - Superadmin to group many-to-many relationship

Database migrations are located in the `database/` directory.

## Security

- All passwords are hashed using bcrypt
- Role-based access control (superadmin/admin/normal/disabled)
- Session-based authentication with session fixation protection
- CSRF protection on all forms
- Rate limiting on login (5 attempts per 15 minutes)
- Session timeout (30 minutes inactivity)
- Secure session cookies (httponly, secure, SameSite=Lax)
- Input validation and sanitization with trim()
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars output escaping
- Server-side file upload validation (MIME type + extension whitelist)
- Activity logging with sensitive data redaction
- Security headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy)
- HTTPS with automatic SSL certificate renewal (Caddy + Let's Encrypt)
- Password complexity requirements (minimum 8 characters)

## Version

Current version: 1.1.1

## License

This project is open source and available under the [MIT License](LICENSE).
