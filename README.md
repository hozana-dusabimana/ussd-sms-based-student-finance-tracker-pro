# Finance Tracker USSD Application

A comprehensive USSD-based finance tracking application that allows users to manage their income, expenses, and track their financial transactions through a simple USSD interface. The application also includes SMS notifications for important financial activities.

## Ensure to get the php laravel 
Clone it on branch main-php-project on this link https://github.com/hozana-dusabimana/ussd-sms-based-student-finance-tracker-pro/tree/main-php-project

## Features

### User Management
- User registration with name and email
- Secure phone number verification
- User profile management

### Financial Tracking
- Real-time balance checking
- Income and expense recording
- Transaction categorization
- Transaction history viewing
- SMS notifications for all transactions

### Category Management
- Custom income categories
- Custom expense categories
- Category-based transaction organization

### SMS Notifications
- Balance updates
- Income transaction confirmations
- Expense transaction confirmations
- Test SMS functionality

## Technical Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (for secure API calls)

### Dependencies
- Laravel's Eloquent ORM
- Carbon for date/time handling
- Africa's Talking API for SMS

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd finance-tracker-with-ussd
```

2. Install dependencies:
```bash
composer install
```

3. Configure the database:
- Create a new MySQL database
- Copy `config/database.example.php` to `config/database.php`
- Update database credentials in `config/database.php`

4. Set up the database tables:
```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    transaction_date TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

5. Configure Africa's Talking API:
- Sign up for an Africa's Talking account
- Get your API key
- Update the API credentials in `ussd_app/ussd.php`:
```php
$apiKey = "your-api-key";
$username = "sandbox"; // or your username
$senderId = "MyMoney ltd";
```

## Usage

### USSD Code
Dial `*384*0074#` to access the application.

### Main Menu Options
1. Check Balance
   - View current balance
   - Receive SMS with balance details
   - View transaction counts

2. Add Income
   - Select income category
   - Enter amount
   - Receive SMS confirmation

3. Add Expense
   - Select expense category
   - Enter amount
   - Receive SMS confirmation

4. View Transactions
   - View last 7 days
   - View last 30 days
   - View last 90 days

5. Manage Categories
   - Add income categories
   - Add expense categories
   - View existing categories

6. Test SMS
   - Send test SMS
   - Verify SMS functionality

7. Exit

### SMS Notifications
The application sends SMS notifications for:
- Balance checks
- Income transactions
- Expense transactions
- Test messages

## Error Handling

The application includes comprehensive error handling for:
- Invalid user inputs
- Database errors
- SMS sending failures
- API communication issues

## Security Features

- Phone number validation
- Input sanitization
- Secure API communication
- Error logging
- Transaction validation

## Development

### Adding New Features
1. Create necessary database tables
2. Add new menu options in `ussd_app/ussd.php`
3. Implement feature logic
4. Add error handling
5. Test thoroughly

### Testing
1. Use the test SMS option to verify SMS functionality
2. Test all menu options
3. Verify error handling
4. Check SMS notifications

## Troubleshooting

### Common Issues
1. SMS Not Sending
   - Verify API credentials
   - Check phone number format
   - Ensure sufficient API credits

2. Database Connection Issues
   - Verify database credentials
   - Check database server status
   - Ensure tables are created

3. USSD Session Issues
   - Verify USSD code
   - Check network connection
   - Clear phone cache

## Support

For support, please:
1. Check the error logs
2. Verify API credentials
3. Ensure proper database setup
4. Contact system administrator

## License

[Your License Information]

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request 
