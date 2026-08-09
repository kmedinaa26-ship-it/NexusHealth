<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("saved_reports", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("module")->default("all");
            $table->date("from");
            $table->date("to");
            $table->string("title");
            $table->string("file_path")->nullable();
            $table->integer("total_events")->default(0);
            $table->integer("total_outliers")->default(0);
            $table->timestamps();
            $table->index(["user_id", "created_at"]);
            $table->index("module");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("saved_reports");
    }
};