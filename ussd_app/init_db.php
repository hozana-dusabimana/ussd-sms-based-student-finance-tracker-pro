<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load database configuration
$config = require __DIR__ . '/config/database.php';

// Initialize database connection
$capsule = new Capsule;
$capsule->addConnection($config['connections'][$config['default']]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Run migrations
require_once __DIR__ . '/database/migrations/create_ussd_users_table.php';

echo "Database initialized successfully!\n"; 