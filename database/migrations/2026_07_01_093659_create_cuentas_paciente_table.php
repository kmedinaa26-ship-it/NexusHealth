<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('cuentas_paciente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('folio')->unique();
            $table->decimal('subtotal_costos', 12, 2)->default(0);
            $table->decimal('subtotal_cobro', 12, 2)->default(0);
            $table->decimal('margen', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total_cobro', 12, 2)->default(0);
            $table->enum('estado', ['abierta', 'pagada', 'parcial', 'incobrable'])->default('abierta')->index();
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'convenio'])->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('cuentas_paciente'); }
};
