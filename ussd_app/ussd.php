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

// Function to send SMS
function sendSMS($phoneNumber, $message) {
    try {
        // Format phone number (remove + if present)
        $phoneNumber = ltrim($phoneNumber, '+');
        
        // Ensure phone number starts with country code
        if (!str_starts_with($phoneNumber, '250')) {
            $phoneNumber = '250' . ltrim($phoneNumber, '0');
        }
        
        // API Configuration
        $apiKey = "atsk_ef972e604cb554ba97b383d651a28795bfbd4733c9db8802d9830cf0968427992154f6a0";
        $username = "sandbox";
        $senderId = "MyMoney ltd";
        $url = "https://api.sandbox.africastalking.com/version1/messaging";
        
        // Log the request details
        error_log("SMS Request Details:");
        error_log("Phone: " . $phoneNumber);
        error_log("Message Length: " . strlen($message));
        error_log("API URL: " . $url);
        error_log("Username: " . $username);
        error_log("Sender ID: " . $senderId);
        
        // Prepare the data
        $data = [
            "username" => $username,
            "to" => $phoneNumber,
            "message" => $message,
            "from" => $senderId
        ];
        
        // Log the request data
        error_log("Request Data: " . print_r($data, true));
        
        // Set up cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                "apiKey: " . $apiKey,
                "Content-Type: application/x-www-form-urlencoded",
                "Accept: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false, // For development only
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_VERBOSE => true
        ]);
        
        // Create a temporary file handle for CURL debug output
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Get the verbose debug information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        // Log the response and debug information
        error_log("SMS Response Code: " . $httpCode);
        error_log("SMS Response: " . $response);
        error_log("SMS Verbose Log: " . $verboseLog);
        
        // Check for errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            error_log("SMS cURL Error: " . $error);
            curl_close($ch);
            fclose($verbose);
            throw new Exception("cURL Error: " . $error);
        }
        
        curl_close($ch);
        fclose($verbose);
        
        // Parse the response
        $result = json_decode($response, true);
        
        // Log the parsed response
        error_log("Parsed Response: " . print_r($result, true));
        
        // Check if the SMS was sent successfully
        if ($httpCode == 201 && isset($result['SMSMessageData']['Recipients'][0]['status']) 
            && $result['SMSMessageData']['Recipients'][0]['status'] == 'Success') {
            error_log("SMS sent successfully to " . $phoneNumber);
            return true;
        } else {
            // Get detailed error information
            $error = isset($result['SMSMessageData']['Recipients'][0]['status']) 
                ? $result['SMSMessageData']['Recipients'][0]['status'] 
                : 'Unknown error';
            $errorMessage = isset($result['SMSMessageData']['Recipients'][0]['message']) 
                ? $result['SMSMessageData']['Recipients'][0]['message'] 
                : 'No error message provided';
            $errorCode = isset($result['SMSMessageData']['Recipients'][0]['number']) 
                ? $result['SMSMessageData']['Recipients'][0]['number'] 
                : 'No error code provided';
            
            error_log("SMS failed to send to " . $phoneNumber);
            error_log("Error Status: " . $error);
            error_log("Error Message: " . $errorMessage);
            error_log("Error Code: " . $errorCode);
            error_log("Full API Response: " . print_r($result, true));
            
            throw new Exception("SMS Error: " . $error . " - " . $errorMessage . " (Code: " . $errorCode . ")");
        }
    } catch (Exception $e) {
        error_log("SMS Exception: " . $e->getMessage());
        error_log("Exception Trace: " . $e->getTraceAsString());
        throw $e; // Re-throw the exception to be handled by the caller
    }
}

// Function to test SMS
function testSMS($phoneNumber) {
    try {
        // Log the test attempt
        error_log("Starting SMS test for phone: " . $phoneNumber);
        
        // Format phone number
        $phoneNumber = ltrim($phoneNumber, '+');
        if (!str_starts_with($phoneNumber, '250')) {
            $phoneNumber = '250' . ltrim($phoneNumber, '0');
        }
        error_log("Formatted phone number: " . $phoneNumber);
        
        // Create test message
        $testMessage = "MyMoney - SMS Test\n";
        $testMessage .= "------------------------\n";
        $testMessage .= "This is a test message.\n";
        $testMessage .= "Time: " . Carbon::now()->format('d/m/Y H:i:s') . "\n";
        $testMessage .= "------------------------\n";
        $testMessage .= "If you receive this, SMS is working!";
        
        // Try to send SMS up to 3 times
        $smsSent = false;
        $attempts = 0;
        $lastError = '';
        
        while (!$smsSent && $attempts < 3) {
            try {
                error_log("Attempt " . ($attempts + 1) . " to send test SMS to " . $phoneNumber);
                $smsSent = sendSMS($phoneNumber, $testMessage);
                if (!$smsSent) {
                    $lastError = "Failed to send SMS on attempt " . ($attempts + 1);
                    error_log($lastError);
                }
            } catch (Exception $e) {
                $lastError = "Exception on attempt " . ($attempts + 1) . ": " . $e->getMessage();
                error_log($lastError);
            }
            
            $attempts++;
            if (!$smsSent && $attempts < 3) {
                sleep(2); // Wait 2 seconds before retrying
            }
        }
        
        if ($smsSent) {
            error_log("Test SMS sent successfully to " . $phoneNumber);
            return true;
        } else {
            error_log("Failed to send test SMS after 3 attempts to " . $phoneNumber . ". Last error: " . $lastError);
            throw new Exception($lastError);
        }
    } catch (Exception $e) {
        error_log("SMS Test Exception: " . $e->getMessage());
        error_log("Exception Trace: " . $e->getTraceAsString());
        throw $e; // Re-throw the exception to be handled by the caller
    }
}

// Handle incoming USSD requests
function handleUssdRequest($sessionId, $serviceCode, $phoneNumber, $text) {
    $response = "";
    
    // Initialize session data
    if (empty($text)) {
        // Check if user exists
        $user = Capsule::table('users')->where('phone', $phoneNumber)->first();
        
        if (!$user) {
            $response = "CON Welcome to Student Finance Tracker\n";
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
            
            $response = "CON Welcome back to Student Finance Tracker\n";
            $response .= "1. Check Balance\n";
            $response .= "2. Add Income (Total: $incomeCount)\n";
            $response .= "3. Add Expense (Total: $expenseCount)\n";
            $response .= "4. View Transactions\n";
            $response .= "5. Manage Categories\n";
            $response .= "6. Test SMS\n";
            $response .= "7. Exit";
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
                    $response = "CON Enter your email:";
                } elseif (count($level) == 3) {
                    // Create new user
                    $userId = Capsule::table('users')->insertGetId([
                        'name' => $level[1],
                        'email' => $level[2],
                        'phone' => $phoneNumber,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    $response = "CON Registration successful!\n";
                    $response .= "1. Check Balance\n";
                    $response .= "2. Add Income\n";
                    $response .= "3. Add Expense\n";
                    $response .= "4. View Transactions\n";
                    $response .= "5. Manage Categories\n";
                    $response .= "6. Exit";
                }
            } else {
                $response = "END Thank you for using  Student Finance Tracker";
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
                    
                    // Send SMS with balance information
                    $smsMessage = "MyMoney - Financial Summary\n";
                    $smsMessage .= "------------------------\n";
                    $smsMessage .= "Balance: rwf " . number_format($balance, 2) . "\n";
                    $smsMessage .= "Income: rwf " . number_format($totalIncome, 2) . "\n";
                    $smsMessage .= "Expenses: rwf " . number_format($totalExpense, 2) . "\n";
                    $smsMessage .= "------------------------\n";
                    $smsMessage .= "Income Tx: " . $incomeCount . "\n";
                    $smsMessage .= "Expense Tx: " . $expenseCount . "\n";
                    $smsMessage .= "Date: " . Carbon::now()->format('d/m/Y H:i') . "\n";
                    $smsMessage .= "------------------------\n";
                    $smsMessage .= "Thank you for using MyMoney!";
                    
                    // Try to send SMS up to 3 times
                    $smsSent = false;
                    $attempts = 0;
                    $lastError = '';
                    
                    while (!$smsSent && $attempts < 3) {
                        try {
                            error_log("Attempt " . ($attempts + 1) . " to send balance SMS to " . $phoneNumber);
                            $smsSent = sendSMS($phoneNumber, $smsMessage);
                            if (!$smsSent) {
                                $lastError = "Failed to send SMS on attempt " . ($attempts + 1);
                                error_log($lastError);
                            }
                        } catch (Exception $e) {
                            $lastError = "Exception on attempt " . ($attempts + 1) . ": " . $e->getMessage();
                            error_log($lastError);
                        }
                        
                        $attempts++;
                        if (!$smsSent && $attempts < 3) {
                            sleep(1); // Wait 1 second before retrying
                        }
                    }
                    
                    $response = "END Your Financial Summary:\n";
                    $response .= "Balance: rwf " . number_format($balance, 2) . "\n";
                    $response .= "Total Income: rwf " . number_format($totalIncome, 2) . " ($incomeCount transactions)\n";
                    $response .= "Total Expenses: rwf " . number_format($totalExpense, 2) . " ($expenseCount transactions)\n";
                    
                    if ($smsSent) {
                        $response .= "\nSMS with your balance has been sent to your phone.";
                        error_log("Successfully sent balance SMS to " . $phoneNumber);
                    } else {
                        $response .= "\nNote: Unable to send SMS. Please try again later.";
                        error_log("Failed to send balance SMS after 3 attempts to " . $phoneNumber . ". Last error: " . $lastError);
                    }
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
                                // Record transaction
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
                                
                                // Send SMS notification for income
                                $smsMessage = "MyMoney - Income Recorded\n";
                                $smsMessage .= "------------------------\n";
                                $smsMessage .= "Amount: rwf " . number_format($amount, 2) . "\n";
                                $smsMessage .= "Category: " . $category->name . "\n";
                                $smsMessage .= "Date: " . Carbon::now()->format('d/m/Y H:i') . "\n";
                                $smsMessage .= "------------------------\n";
                                $smsMessage .= "Thank you for using MyMoney!";
                                
                                $smsSent = sendSMS($phoneNumber, $smsMessage);
                                if (!$smsSent) {
                                    error_log("Failed to send income SMS to " . $phoneNumber);
                                }
                                
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
                                // Record transaction
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
                                
                                // Send SMS notification for expense
                                $smsMessage = "MyMoney - Expense Recorded\n";
                                $smsMessage .= "------------------------\n";
                                $smsMessage .= "Amount: rwf " . number_format($amount, 2) . "\n";
                                $smsMessage .= "Category: " . $category->name . "\n";
                                $smsMessage .= "Date: " . Carbon::now()->format('d/m/Y H:i') . "\n";
                                $smsMessage .= "------------------------\n";
                                $smsMessage .= "Thank you for using MyMoney!";
                                
                                $smsSent = sendSMS($phoneNumber, $smsMessage);
                                if (!$smsSent) {
                                    error_log("Failed to send expense SMS to " . $phoneNumber);
                                }
                                
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
                
                case 5: // Manage Categories
                    if (count($level) == 1) {
                        $response = "CON Manage Categories:\n";
                        $response .= "1. Add Income Category\n";
                        $response .= "2. Add Expense Category\n";
                        $response .= "3. View Categories\n";
                        $response .= "4. Back to Main Menu";
                    } elseif (count($level) == 2) {
                        switch ($level[1]) {
                            case 1: // Add Income Category
                                $response = "CON Enter new income category name:";
                                break;
                            case 2: // Add Expense Category
                                $response = "CON Enter new expense category name:";
                                break;
                            case 3: // View Categories
                                $incomeCategories = Capsule::table('categories')
                                    ->where('type', 'income')
                                    ->get();
                                
                                $expenseCategories = Capsule::table('categories')
                                    ->where('type', 'expense')
                                    ->get();
                                
                                $response = "END Categories:\n\nIncome Categories:\n";
                                foreach ($incomeCategories as $category) {
                                    $response .= "- " . $category->name . "\n";
                                }
                                
                                $response .= "\nExpense Categories:\n";
                                foreach ($expenseCategories as $category) {
                                    $response .= "- " . $category->name . "\n";
                                }
                                break;
                            case 4: // Back to Main Menu
                                $response = "CON Welcome back to Student Finance Tracker\n";
                                $response .= "1. Check Balance\n";
                                $response .= "2. Add Income (Total: $incomeCount)\n";
                                $response .= "3. Add Expense (Total: $expenseCount)\n";
                                $response .= "4. View Transactions\n";
                                $response .= "5. Manage Categories\n";
                                $response .= "6. Exit";
                                break;
                            default:
                                $response = "END Invalid option. Please try again.";
                        }
                    } elseif (count($level) == 3) {
                        $categoryName = trim($level[2]);
                        if (empty($categoryName)) {
                            $response = "END Invalid category name. Please try again.";
                        } else {
                            $type = ($level[1] == 1) ? 'income' : 'expense';
                            
                            // Check if category already exists
                            $exists = Capsule::table('categories')
                                ->where('name', $categoryName)
                                ->where('type', $type)
                                ->exists();
                            
                            if ($exists) {
                                $response = "END Category already exists. Please try again.";
                            } else {
                                // Add new category
                                Capsule::table('categories')->insert([
                                    'name' => $categoryName,
                                    'type' => $type,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                                
                                $response = "END New " . $type . " category '" . $categoryName . "' has been added successfully.";
                            }
                        }
                    }
                    break;
                
                case 6: // Test SMS
                    try {
                        $smsSent = testSMS($phoneNumber);
                        if ($smsSent) {
                            $response = "END Test SMS has been sent to your phone.\n";
                            $response .= "Please check if you received it.\n";
                            $response .= "If you don't receive it within 1 minute, please try again.";
                        }
                    } catch (Exception $e) {
                        $response = "END Failed to send test SMS.\n";
                        $response .= "Error: " . $e->getMessage() . "\n";
                        $response .= "Please check the error logs for more details.\n";
                        $response .= "You can try again in a few minutes.";
                    }
                    break;
                
                case 7: // Exit
                    $response = "END Thank you for using Student Finance Tracker";
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
