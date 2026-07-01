<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('predicciones_clinicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index(); // Referencia libre al paciente (Mongo/OTra)
            $table->foreignId('modelo_version_id')->constrained('ml_modelos_versiones');
            $table->json('datos_entrada');
            $table->decimal('probabilidad', 5, 4);
            $table->string('prediccion');
            $table->decimal('score_confianza', 5, 4)->nullable();
            $table->enum('estado', ['activa', 'cerrada', 'error'])->default('activa')->index();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('predicciones_clinicas'); }
};
