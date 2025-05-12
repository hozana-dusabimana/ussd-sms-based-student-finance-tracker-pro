<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

// Load database configuration
$config = require __DIR__ . '/config/database.php';

// Initialize database connection
$capsule = new Capsule;
$capsule->addConnection($config['connections'][$config['default']]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Handle incoming USSD requests
function handleUssdRequest($sessionId, $serviceCode, $phoneNumber, $text) {
    $response = "";
    
    // Initialize session data
    if (empty($text)) {
        // Check if user exists
        $user = Capsule::table('users')->where('phone', $phoneNumber)->first();
        
        if (!$user) {
            $response = "CON Welcome to Finance Tracker\n";
            $response .= "Please register to continue:\n";
            $response .= "1. Register\n";
            $response .= "2. Exit";
        } else {
            // Get user's transaction counts
            $incomeCount = Capsule::table('transactions')
                ->where('user_id', $user->id)
                ->where('type', 'income')
                ->count();
            
            $expenseCount = Capsule::table('transactions')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->count();
            
            $response = "CON Welcome back to Finance Tracker\n";
            $response .= "1. Check Balance\n";
            $response .= "2. Add Income (Total: $incomeCount)\n";
            $response .= "3. Add Expense (Total: $expenseCount)\n";
            $response .= "4. View Transactions\n";
            $response .= "5. Exit";
        }
    } else {
        // Handle user input
        $level = explode("*", $text);
        
        // Check if user exists
        $user = Capsule::table('users')->where('phone', $phoneNumber)->first();
        
        if (!$user) {
            // Registration flow
            if ($level[0] == 1) {
                if (count($level) == 1) {
                    $response = "CON Enter your name:";
                } elseif (count($level) == 2) {
                    // Create new user
                    $userId = Capsule::table('users')->insertGetId([
                        'name' => $level[1],
                        'phone' => $phoneNumber,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    $response = "CON Registration successful!\n";
                    $response .= "1. Check Balance\n";
                    $response .= "2. Add Income\n";
                    $response .= "3. Add Expense\n";
                    $response .= "4. View Transactions\n";
                    $response .= "5. Exit";
                }
            } else {
                $response = "END Thank you for using Finance Tracker";
            }
        } else {
            switch ($level[0]) {
                case 1: // Check Balance
                    $totalIncome = Capsule::table('transactions')
                        ->where('user_id', $user->id)
                        ->where('type', 'income')
                        ->sum('amount');
                    
                    $totalExpense = Capsule::table('transactions')
                        ->where('user_id', $user->id)
                        ->where('type', 'expense')
                        ->sum('amount');
                    
                    $balance = $totalIncome - $totalExpense;
                    
                    // Get transaction counts
                    $incomeCount = Capsule::table('transactions')
                        ->where('user_id', $user->id)
                        ->where('type', 'income')
                        ->count();
                    
                    $expenseCount = Capsule::table('transactions')
                        ->where('user_id', $user->id)
                        ->where('type', 'expense')
                        ->count();
                    
                    $response = "END Your Financial Summary:\n";
                    $response .= "Balance: rwf " . number_format($balance, 2) . "\n";
                    $response .= "Total Income: rwf " . number_format($totalIncome, 2) . " ($incomeCount transactions)\n";
                    $response .= "Total Expenses: rwf " . number_format($totalExpense, 2) . " ($expenseCount transactions)";
                    break;
                
                case 2: // Add Income
                    if (count($level) == 1) {
                        // Get categories for income
                        $categories = Capsule::table('categories')
                            ->where('type', 'income')
                            ->get();
                        
                        $response = "CON Select income category:\n";
                        foreach ($categories as $index => $category) {
                            $response .= ($index + 1) . ". " . $category->name . "\n";
                        }
                    } elseif (count($level) == 2) {
                        $response = "CON Enter income amount:";
                    } elseif (count($level) == 3) {
                        $amount = floatval($level[2]);
                        if ($amount <= 0) {
                            $response = "END Invalid amount. Please try again.";
                        } else {
                            // Get selected category
                            $categoryIndex = intval($level[1]) - 1;
                            $category = Capsule::table('categories')
                                ->where('type', 'income')
                                ->skip($categoryIndex)
                                ->first();
                            
                            if (!$category) {
                                $response = "END Invalid category. Please try again.";
                            } else {
                                Capsule::table('transactions')->insert([
                                    'user_id' => $user->id,
                                    'category_id' => $category->id,
                                    'type' => 'income',
                                    'amount' => $amount,
                                    'description' => $category->name,
                                    'transaction_date' => Carbon::now(),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                                
                                $response = "END Income of rwf " . number_format($amount, 2) . " has been recorded in " . $category->name;
                            }
                        }
                    }
                    break;
                
                case 3: // Add Expense
                    if (count($level) == 1) {
                        // Get categories for expense
                        $categories = Capsule::table('categories')
                            ->where('type', 'expense')
                            ->get();
                        
                        $response = "CON Select expense category:\n";
                        foreach ($categories as $index => $category) {
                            $response .= ($index + 1) . ". " . $category->name . "\n";
                        }
                    } elseif (count($level) == 2) {
                        $response = "CON Enter expense amount:";
                    } elseif (count($level) == 3) {
                        $amount = floatval($level[2]);
                        if ($amount <= 0) {
                            $response = "END Invalid amount. Please try again.";
                        } else {
                            // Get selected category
                            $categoryIndex = intval($level[1]) - 1;
                            $category = Capsule::table('categories')
                                ->where('type', 'expense')
                                ->skip($categoryIndex)
                                ->first();
                            
                            if (!$category) {
                                $response = "END Invalid category. Please try again.";
                            } else {
                                Capsule::table('transactions')->insert([
                                    'user_id' => $user->id,
                                    'category_id' => $category->id,
                                    'type' => 'expense',
                                    'amount' => $amount,
                                    'description' => $category->name,
                                    'transaction_date' => Carbon::now(),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                                
                                $response = "END Expense of rwf " . number_format($amount, 2) . " has been recorded in " . $category->name;
                            }
                        }
                    }
                    break;
                
                case 4: // View Transactions
                    if (count($level) == 1) {
                        $response = "CON Select period:\n";
                        $response .= "1. Last 7 days\n";
                        $response .= "2. Last 30 days\n";
                        $response .= "3. Last 90 days";
                    } elseif (count($level) == 2) {
                        $days = 7;
                        if ($level[1] == 2) $days = 30;
                        elseif ($level[1] == 3) $days = 90;
                        
                        $transactions = Capsule::table('transactions')
                            ->join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->where('transactions.user_id', $user->id)
                            ->where('transactions.created_at', '>=', Carbon::now()->subDays($days))
                            ->orderBy('transactions.created_at', 'desc')
                            ->limit(5)
                            ->get(['transactions.*', 'categories.name as category_name']);
                        
                        if ($transactions->isEmpty()) {
                            $response = "END No transactions found in the last $days days.";
                        } else {
                            $response = "END Recent transactions:\n";
                            foreach ($transactions as $transaction) {
                                $type = $transaction->type == 'income' ? '+' : '-';
                                $date = Carbon::parse($transaction->created_at)->format('d/m/Y');
                                $response .= "$date: $type rwf " . number_format($transaction->amount, 2) . " (" . $transaction->category_name . ")\n";
                            }
                        }
                    }
                    break;
                
                case 5: // Exit
                    $response = "END Thank you for using Finance Tracker";
                    break;
                
                default:
                    $response = "END Invalid option. Please try again.";
            }
        }
    }
    
    return $response;
}

// Process incoming USSD request
if (isset($_POST['sessionId']) && isset($_POST['serviceCode']) && 
    isset($_POST['phoneNumber']) && isset($_POST['text'])) {
    
    $sessionId = $_POST['sessionId'];
    $serviceCode = $_POST['serviceCode'];
    $phoneNumber = $_POST['phoneNumber'];
    $text = $_POST['text'];
    
    echo handleUssdRequest($sessionId, $serviceCode, $phoneNumber, $text);
} 