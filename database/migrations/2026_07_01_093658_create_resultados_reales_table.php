<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('resultados_reales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediccion_id')->constrained('predicciones_clinicas')->cascadeOnDelete();
            $table->string('resultado_real');
            $table->integer('dias_hospitalizacion')->nullable();
            $table->decimal('costo_real', 12, 2)->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->text('notas_doctor')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('resultados_reales'); }
};
