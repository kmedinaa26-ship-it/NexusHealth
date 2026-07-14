<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('alertas_ml', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_version_id')->nullable()->constrained('ml_modelos_versiones')->nullOnDelete();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->enum('tipo', ['riesgo_alto', 'costo_excedido', 'modelo_degradado', 'error_prediccion'])->index();
            $table->text('mensaje');
            $table->boolean('leida')->default(false)->index();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('alertas_ml'); }
};
