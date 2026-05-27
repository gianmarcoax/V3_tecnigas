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
    Schema::create('goal_tiers', function (Blueprint $table) {
        $table->id();
        $table->enum('owner_type', ['group', 'individual']);        // grupal o individual
        $table->enum('area', ['ventas', 'servicio_tecnico']);       // área
        $table->enum('period_type', ['weekly', 'monthly']);         // semanal o mensual
        $table->enum('shift', ['manana', 'tarde'])->nullable();     // solo para grupales
        $table->string('label', 10);                                // A, B, C, Oro, etc.
        $table->decimal('sales_goal', 12, 2);                      // meta (ventas S/ o cant. reparaciones)
        $table->decimal('bonus_amount', 10, 2);                    // bono si alcanza este nivel
        $table->integer('sort_order')->default(0);                  // orden visual

        // Meta única por combinación (no puede haber dos niveles con la misma meta)
        $table->unique(['owner_type', 'area', 'period_type', 'shift', 'sales_goal'], 'unique_goal_tier');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_tiers');
    }
};
