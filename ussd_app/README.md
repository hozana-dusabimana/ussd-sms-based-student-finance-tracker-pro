# Finance Tracker USSD Application

A USSD-based financial management system that allows users to manage their finances using their mobile phones.

## Features

- Check account balance
- Add income transactions
- Add expense transactions
- View transaction history
- Mobile-friendly interface
- Real-time updates

## Prerequisites

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Africa's Talking API account

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/finance-tracker-ussd.git
```

2. Install dependencies:
```bash
composer install
```

3. Copy the environment file and configure it:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your Africa's Talking credentials in the `.env` file:
```
AFRICAS_TALKING_USERNAME=your_username
AFRICAS_TALKING_API_KEY=your_api_key
USSD_SERVICE_CODE=*123#  # Replace with your actual USSD code
```

## Usage

1. Dial your USSD code (configured in .env)
2. Follow the on-screen menu options to:
   - Check your balance
   - Add income
   - Add expenses
   - View transaction history

## Menu Structure

```
1. Check Balance
2. Add Income
3. Add Expense
4. View Transactions
5. Exit
```

## Security

- All transactions are encrypted
- User sessions are securely managed
- API credentials are stored in environment variables

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
