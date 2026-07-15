<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_medications')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('encounter_type', ['cita', 'urgencia', 'hospitalizacion', 'cirugia']);
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->enum('status', ['abierta', 'cerrada', 'anulada'])->default('abierta');
            
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('taxes', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('set null');
            $table->float('insurance_coverage')->default(0);
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_accounts');
    }
};
