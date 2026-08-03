<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
¿        DB::statement("ALTER TABLE medical_alerts MODIFY type ENUM('Crítico', 'Advertencia', 'Info', 'Código Azul')");
    }

    public function down()
    {
¿        DB::statement("ALTER TABLE medical_alerts MODIFY type ENUM('Crítico', 'Advertencia', 'Info')");
    }
};
