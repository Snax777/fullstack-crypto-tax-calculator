<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();  // Auto-incrementing ID
            $table->string('name');  // Wallet name (like "My Bitcoin Wallet")
            $table->string('address')->unique();  // Mock wallet address
            $table->string('crypto_type');  // BTC, ETH, etc.
            $table->decimal('balance', 18, 8)->default(0);  // Crypto balance
            $table->timestamps();  // created_at and updated_at columns
        }); // Creates `wallets` database table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
