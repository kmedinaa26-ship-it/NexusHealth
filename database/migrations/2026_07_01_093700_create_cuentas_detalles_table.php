<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('cuentas_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas_paciente')->cascadeOnDelete();
            $table->enum('tipo_referencia', ['costo_evento', 'honorario_medico', 'servicio_fijo']);
            $table->unsignedBigInteger('referencia_id');
            $table->string('descripcion');
            $table->decimal('costo_real', 10, 2)->default(0);
            $table->decimal('precio_cobro', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('cuentas_detalles'); }
};
