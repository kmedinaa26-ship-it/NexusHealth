<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('predicciones_clinicas', function (Blueprint $table) {
            if (!Schema::hasColumn('predicciones_clinicas', 'aprobado_para_entrenamiento')) {
                $table->boolean('aprobado_para_entrenamiento')->default(0)->after('estado');
            }
        });
    }

    public function down()
    {
        Schema::table('predicciones_clinicas', function (Blueprint $table) {
            $table->dropColumn('aprobado_para_entrenamiento');
        });
    }
};
