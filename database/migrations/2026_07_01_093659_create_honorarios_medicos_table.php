<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('honorarios_medicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->foreignId('doctor_id')->constrained('users'); // Doctor es un usuario
            $table->foreignId('prediccion_id')->nullable()->constrained('predicciones_clinicas')->nullOnDelete();
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'pendiente'])->default('pendiente');
            $table->boolean('pagado')->default(false);
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('honorarios_medicos'); }
};
