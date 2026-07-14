<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ml_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('concept');
            $table->decimal('amount', 12, 2);
            $table->string('source_module');
            $table->string('source_detail')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ml_feeds'); }
};
