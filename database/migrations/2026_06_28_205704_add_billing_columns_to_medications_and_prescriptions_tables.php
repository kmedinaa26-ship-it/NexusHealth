<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar precios a medicamentos si no existen
        if (!Schema::hasColumn('medications', 'sale_price')) {
            Schema::table('medications', function (Blueprint $table) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('id');
                $table->decimal('sale_price', 10, 2)->default(0)->after('cost_price');
                $table->boolean('is_insumo')->default(false)->after('sale_price');
            });
        }

        // Agregar control a recetas si no existen
        if (!Schema::hasColumn('prescriptions', 'account_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->constrained('patient_accounts')->onDelete('set null')->after('id');
                $table->foreignId('dispensed_by')->nullable()->constrained('users')->onDelete('set null')->after('account_id');
                $table->timestamp('dispensed_at')->nullable()->after('dispensed_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'sale_price', 'is_insumo']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['dispensed_by']);
            $table->dropColumn(['account_id', 'dispensed_by', 'dispensed_at']);
        });
    }
};
