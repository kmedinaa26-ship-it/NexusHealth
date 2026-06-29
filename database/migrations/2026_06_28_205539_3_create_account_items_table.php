<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('patient_accounts')->onDelete('cascade');
            $table->enum('type', ['servicio', 'producto', 'insumo', 'honorario', 'sala']);
            $table->string('concept');
            
            // Relación polimórfica por si el cargo viene de un medicamento, un servicio, etc.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            
            $table->enum('source_module', ['farmacia', 'medico', 'quirofano', 'enfermeria', 'sistema'])->default('sistema');
            $table->foreignId('prescribed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_items');
    }
};
