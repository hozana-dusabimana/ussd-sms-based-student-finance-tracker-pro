<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Create users table if it doesn't exist
if (!Capsule::schema()->hasTable('users')) {
    Capsule::schema()->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('phone')->unique();
        $table->timestamps();
    });
}

// Create categories table if it doesn't exist
if (!Capsule::schema()->hasTable('categories')) {
    Capsule::schema()->create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('type', ['income', 'expense']);
        $table->timestamps();
    });

    // Insert default categories
    Capsule::table('categories')->insert([
        ['name' => 'General Income', 'type' => 'income', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ['name' => 'General Expense', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
    ]);
}

// Create transactions table if it doesn't exist
if (!Capsule::schema()->hasTable('transactions')) {
    Capsule::schema()->create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->enum('type', ['income', 'expense']);
        $table->decimal('amount', 10, 2);
        $table->text('description')->nullable();
        $table->date('transaction_date');
        $table->timestamps();
    });
} 