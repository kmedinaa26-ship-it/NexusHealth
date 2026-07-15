<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confusion_matrices', function (Blueprint $table) {
            $table->id();
            $table->enum('model_type', ['logistic', 'decision_tree', 'random_forest', 'svm', 'mora_financiera']);
            $table->integer('tp')->default(0);
            $table->integer('tn')->default(0);
            $table->integer('fp')->default(0);
            $table->integer('fn')->default(0);
            $table->float('accuracy')->nullable();
            $table->float('precision')->nullable();
            $table->float('recall')->nullable();
            $table->float('f1_score')->nullable();
            $table->float('mse')->nullable();
            $table->float('rmse')->nullable();
            $table->float('mae')->nullable();
            $table->date('evaluated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confusion_matrices');
    }
};
