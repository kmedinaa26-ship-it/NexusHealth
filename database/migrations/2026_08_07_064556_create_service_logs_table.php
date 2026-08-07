<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('event_type');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_identifier')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->unsignedTinyInteger('start_hour')->nullable();
            $table->string('status')->default('in_progress');
            $table->boolean('is_outlier')->default(false);
            $table->decimal('outlier_z_score', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['module', 'event_type']);
            $table->index('started_at');
        });
    }
    public function down(): void { Schema::dropIfExists('service_logs'); }
};
