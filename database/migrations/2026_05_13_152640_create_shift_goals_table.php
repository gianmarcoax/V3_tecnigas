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
        Schema::create('shift_goals', function (Blueprint $table) {
            $table->id();
            $table->enum('shift', ['manana', 'tarde']);
            $table->enum('period_type', ['weekly', 'monthly']);
            $table->decimal('sales_goal', 12, 2);     // meta de ventas en soles
            $table->decimal('group_bonus', 10, 2);    // bono grupal si se cumple
            $table->timestamps();

            $table->unique(['shift', 'period_type']); // una config por turno+período
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_goals');
    }
};
