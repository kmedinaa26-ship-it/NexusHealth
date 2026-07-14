<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('explicacion_prediccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediccion_id')->constrained('predicciones_clinicas')->cascadeOnDelete();
            $table->string('variable');
            $table->decimal('peso', 8, 4);
            $table->string('impacto');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('explicacion_prediccion'); }
};
