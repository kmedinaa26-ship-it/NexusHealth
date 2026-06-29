<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('patient_accounts')->onDelete('cascade');
            $table->enum('payment_type', ['direct_sale', 'account_payment', 'account_close']);
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['efectivo', 'tarjeta', 'transferencia', 'seguro'])->default('efectivo');
            $table->string('reference')->nullable(); // Folio o autorización
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
