<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('patients', 'age')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->integer('age')->default(0)->after('name');
            });
        }

        // Asignar edades aleatorias realistas basadas en triage
        DB::statement("UPDATE patients SET age = CASE 
            WHEN triage_level = 'verde' THEN FLOOR(18 + RAND() * 27)
            WHEN triage_level = 'amarillo' THEN FLOOR(30 + RAND() * 30)
            WHEN triage_level = 'rojo' THEN FLOOR(45 + RAND() * 35)
            WHEN triage_level = 'negro' THEN FLOOR(55 + RAND() * 30)
            ELSE FLOOR(20 + RAND() * 40)
        END WHERE age = 0 OR age IS NULL");

        // Corregir fechas de admision para que tengan diferencia real
        DB::statement("UPDATE admissions SET updated_at = DATE_ADD(created_at, INTERVAL FLOOR(8 + RAND() * 500) HOUR) WHERE updated_at <= created_at");
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }
};
