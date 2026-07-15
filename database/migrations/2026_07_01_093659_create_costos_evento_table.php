<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('costos_evento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->foreignId('prediccion_id')->nullable()->constrained('predicciones_clinicas')->nullOnDelete();
            $table->enum('tipo', ['insumo', 'papel', 'gas_medico', 'otro'])->default('insumo');
            $table->string('descripcion');
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('costo_total', 12, 2);
            $table->foreignId('registrado_por')->constrained('users'); // Esta sí existe en MySQL
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('costos_evento'); }
};
