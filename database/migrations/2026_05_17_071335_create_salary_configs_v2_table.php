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
        // Eliminar la versión anterior (creada en 2026_05_13_152635) si existe
        Schema::dropIfExists('salary_configs');

        Schema::create('salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('base_salary', 10, 2)->default(0);       // sueldo contractual puro
            $table->decimal('extra_bonus', 10, 2)->default(0);        // bono fijo por cargo
            $table->string('extra_bonus_reason')->nullable();          // motivo explícito
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_configs_v2');
    }
};
