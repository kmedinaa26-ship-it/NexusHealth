<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('metricas_drift_modelo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_version_id')->constrained('ml_modelos_versiones');
            $table->decimal('f1_score_actual', 8, 4);
            $table->decimal('accuracy_actual', 8, 4);
            $table->integer('cantidad_datos_evaluados');
            $table->boolean('drift_detectado')->default(false);
            $table->timestamp('fecha_evaluacion');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('metricas_drift_modelo'); }
};
