# Weekly Savings Plan App

A simple web application to track weekly savings payments for individuals or groups.

## Description

The Weekly Savings Plan App helps users track their savings goals on a weekly basis. Each week has a savings goal that increases gradually (Week 1 = $1,000, Week 2 = $2,000, etc.). The app allows multiple users to participate with customizable multipliers and tracks payments against weekly goals.

## Features

- **Weekly Savings Plan**: Visual calendar view showing savings progress for each week of the year
- **Payment Tracking**: Record payments and track which weeks are paid, partially paid, or unpaid
- **Multi-User Support**: Multiple users can participate, each with their own savings multiplier
- **Activities**: Extra charges or expenses that are paid after all weekly goals are covered
- **Role-Based Access**: Admin and normal user roles with appropriate permissions
- **User Management**: Create, edit, and manage users with unique usernames
- **Password Security**: Secure login system with password change functionality
- **Bilingual Support**: Available in English and Spanish
- **Responsive Design**: Works on desktop and mobile devices
- **Color-Coded Progress**: Green (paid), yellow (partial), red (unpaid) indicators

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

2. Start the application using Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. Access the application at `http://localhost:8490`

## Default Login

- **Username**: admin
- **Password**: password

You will be prompted to change your password on first login.

## Project Structure

```
weekly-savings-plan-app/
├── config/           # Configuration files
│   ├── config.php    # App configuration
│   └── database.php  # Database connection
├── controllers/      # Application controllers
│   ├── ActivityController.php
│   ├── Auth.php
│   ├── SavingController.php
│   └── UserController.php
├── database/         # Database migrations and schema
│   ├── schema.sql
│   ├── migration_add_activities.sql
│   ├── migration_add_multiplier.sql
│   ├── migration_add_role_password.sql
│   └── migration_rename_fields.sql
├── lang/             # Language files
│   ├── en.php        # English translations
│   └── es.php        # Spanish translations
├── models/           # Data models
│   ├── Activity.php
│   ├── Saving.php
│   ├── User.php
│   └── WeeklySaving.php
├── uploads/          # User uploads directory
├── views/            # View templates
│   ├── activities/   # Activity views
│   ├── users/        # User views
│   ├── create.php
│   ├── edit.php
│   ├── footer.php
│   ├── header.php
│   ├── list.php
│   ├── login.php
│   └── weekly.php
├── docker-compose.yml
├── Dockerfile
├── index.php         # Main entry point
└── locale.php        # Locale management
```

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

### User Management (Admin Only)
- Create new users with unique usernames
- Set savings multipliers for each user
- Reset user passwords
- Enable/disable user accounts

### Activities
- Create extra charges or expenses
- Activities are paid after all weekly goals are covered
- Track activity payments separately from weekly goals

## Customization

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

Database migrations are located in the `database/` directory.

## Security

- All passwords are hashed using bcrypt
- Role-based access control (admin/normal/disabled)
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention using prepared statements

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
