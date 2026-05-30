<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('providers', function (Blueprint $table) {
            $table->integer('delivery_score')->default(5);
            $table->integer('price_score')->default(5);
            $table->integer('quality_score')->default(5);
            $table->integer('total_orders')->default(0);
            $table->integer('late_deliveries')->default(0);
            $table->decimal('avg_delivery_days', 4, 1)->default(0);
        });
    }
    public function down(): void {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['delivery_score', 'price_score', 'quality_score', 'total_orders', 'late_deliveries', 'avg_delivery_days']);
        });
    }
};
