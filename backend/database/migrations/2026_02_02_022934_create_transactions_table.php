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
    Schema::create(
      'transactions',
      function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->enum('type', ['BUY', 'SELL', 'TRADE']);
        $table->string('coin', 10);
        $table->decimal('quantity', 20, 18);
        $table->decimal('price_zar', 20, 2);
        $table->decimal('fee', 20, 2)->nullable();
        $table->string('session_id')->nullable();
        $table->timestamps();
        $table->index('session_id');
        $table->index('date');
        $table->index(['session_id', 'date']);
      }
    );
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
