<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_cleanliness_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_local_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('score', 3, 1);  // 0.0 – 2.0
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_local_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cleanliness_scores');
    }
};
