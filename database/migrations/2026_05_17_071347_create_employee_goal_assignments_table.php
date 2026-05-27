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
    Schema::create('employee_goal_assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->onDelete('cascade');
        $table->enum('period_type', ['weekly', 'monthly']);
        $table->foreignId('goal_tier_group_id')->nullable()       // referencia a qué "grupo de tiers" aplica
              ->comment('owner_type+area+period_type+shift que aplica a este empleado');
        // Guardamos los parámetros directamente para simplificar queries
        $table->enum('area', ['ventas', 'servicio_tecnico'])->nullable();
        $table->timestamps();

        $table->unique(['employee_id', 'period_type']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_goal_assignments');
    }
};
