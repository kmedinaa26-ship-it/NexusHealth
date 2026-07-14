<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        Schema::table('explicacion_prediccion', function (Blueprint $table) {
            if (!Schema::hasColumn('explicacion_prediccion', 'variables')) {
                $table->text('variables')->nullable()->after('prediccion_id');
            }
        });
    }
    public function down()
    {
        Schema::table('explicacion_prediccion', function (Blueprint $table) {
            if (Schema::hasColumn('explicacion_prediccion', 'variables')) {
                $table->dropColumn('variables');
            }
        });
    }
};
