# Weekly Savings Plan App

A simple web application to track weekly savings payments for individuals or groups.

## Description

The Weekly Savings Plan App helps users track their savings goals on a weekly basis. Each week has a savings goal that increases gradually (Week 1 = $1,000, Week 2 = $2,000, etc.). The app allows multiple users to participate with customizable multipliers and tracks payments against weekly goals.

## Default Login

- **Username**: admin
- **Password**: password

You will be prompted to change your password on first login.

## Features

- **Weekly Savings Plan**: Visual calendar view showing savings progress for each week of the year
- **Payment Tracking**: Record payments and track which weeks are paid, partially paid, or unpaid
- **Multi-User Support**: Multiple users can participate, each with their own savings multiplier
- **Activities**: Extra charges or expenses that are paid after all weekly goals are covered
- **Role-Based Access**: Admin and normal user roles with appropriate permissions
- **User Management**: Create, edit, and manage users with unique usernames
- **Password Security**: Secure login system with password change functionality
- **Activity Logs**: Track all user actions with timestamp, action type, and details (admin only)
- **HTTPS Support**: Automatic SSL certificates via Caddy reverse proxy
- **Bilingual Support**: Available in English and Spanish
- **Responsive Design**: Works on desktop and mobile devices
- **Color-Coded Progress**: Green (paid), yellow (partial), red (unpaid) indicators

## Usage

### Weekly Plan
- View savings progress for each week of the year
- Filter by user or view all users combined
- See color-coded status for each week
- Track activities that are paid after weekly goals

### Payments
- Record new payments with user, amount, and payment method
- Filter payments by user, method, or month
- Edit or delete existing payments (admin only)
- Verify payments to lock them from further edits

### User Management (Admin Only)
- Create new users with unique usernames
- Set savings multipliers for each user
- Reset user passwords
- Enable/disable user accounts

### Activities
- Create extra charges or expenses
- Activities are paid after all weekly goals are covered
- Track activity payments separately from weekly goals

### Activity Logs (Admin Only)
- View a log of all actions performed in the system
- Filter by date range, user, or action type
- Track who performed each action vs. who owns the record
- Sensitive data (passwords) is automatically redacted

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
│   └── database.php      # Database connection (reads env vars)
├── controllers/          # Application controllers
│   ├── ActivityController.php
│   ├── Auth.php
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
│   ├── Saving.php
│   ├── User.php
│   └── WeeklySaving.php
├── views/                # View templates
│   ├── activities/
│   ├── logs/
│   ├── users/
│   ├── create.php
│   ├── edit.php
│   ├── footer.php
│   ├── header.php
│   ├── list.php
│   ├── login.php
│   └── weekly.php
├── caddy/                # Caddy reverse proxy config
│   └── Caddyfile
├── uploads/              # User uploads directory
├── secrets/              # Docker secrets (gitignored)
├── local/                # Local config with sensitive data (gitignored)
├── .env.example          # Example environment variables
├── .env                  # Actual environment variables (gitignored)
├── docker-compose.yml
├── Dockerfile
├── index.php             # Main entry point
└── locale.php            # Locale and timezone management
```

## Customization

### Timezone
The application uses `America/Bogota` (UTC-5) by default. To change it, edit `locale.php`:
```php
date_default_timezone_set('America/New_York');
```
See [DEPLOYMENT.md](DEPLOYMENT.md) for a list of timezone identifiers.

### Payment Methods
The app supports two payment methods:
- Cash
- Bank Transfer

To modify payment methods, update the locale files in `lang/en.php` and `lang/es.php`.

### Multiplier
The multiplier determines how many times a user saves per week. For example:
- Multiplier 1: Standard weekly goal
- Multiplier 2: Double the weekly goal
- Multiplier 1.5: 1.5 times the weekly goal

## Database

The application uses MySQL 8.0 with the following main tables:
- `users` - User accounts and settings
- `savings` - Payment records
- `activities` - Extra charges or expenses
- `activity_logs` - Action audit trail

Database migrations are located in the `database/` directory.

## Security

- All passwords are hashed using bcrypt
- Role-based access control (admin/normal/disabled)
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention using prepared statements
- Activity logging with sensitive data redaction
- HTTPS with automatic SSL certificate renewal (Caddy + Let's Encrypt)

## Browser Support

The application supports all modern browsers:
- Chrome
- Firefox
- Safari
- Edge

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support or questions, please open an issue on the GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
