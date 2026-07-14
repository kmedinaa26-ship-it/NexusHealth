<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('ml_modelos_versiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('algoritmo');
            $table->string('ruta_archivo');
            $table->decimal('metrica_f1', 8, 4)->default(0);
            $table->decimal('metrica_accuracy', 8, 4)->default(0);
            $table->enum('estado', ['activo', 'inactivo', 'descartado'])->default('activo');
            $table->integer('version')->default(1);
            $table->timestamp('trained_at')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('ml_modelos_versiones'); }
};
