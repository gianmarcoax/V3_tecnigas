<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['weekly', 'monthly']);
            $table->decimal('sales_goal', 12, 2);      // meta individual en soles
            $table->decimal('individual_bonus', 10, 2); // bono si la cumple
            $table->timestamps();

            $table->unique(['employee_id', 'period_type']); // una config por empleado+período
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_goals');
    }
};
