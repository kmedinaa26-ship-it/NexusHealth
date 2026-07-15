<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('status', 50)->default('Pendiente')->change();
        });
    }

    public function down(): void
    {
        // No hacemos nada en el down para no romper los datos
    }
};
