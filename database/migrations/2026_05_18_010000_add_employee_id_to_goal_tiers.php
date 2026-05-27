<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goal_tiers', function (Blueprint $table) {
            // FK opcional: si está presente, este tier pertenece a ese empleado (bono individual)
            // Si es null, es una plantilla/tier grupal genérico
            $table->unsignedBigInteger('employee_local_id')->nullable()->after('id');
            $table->foreign('employee_local_id')->references('id')->on('employees')->onDelete('cascade');

            // Quitar el unique constraint viejo (incluía owner_type,area,period_type,shift,sales_goal)
            // porque ahora puede haber múltiples tiers con la misma meta para distintos empleados
            $table->dropUnique('unique_goal_tier');

            // Nuevo unique: incluye employee_local_id para que cada empleado pueda tener sus propias metas
            $table->unique(
                ['employee_local_id', 'owner_type', 'area', 'period_type', 'shift', 'sales_goal'],
                'unique_goal_tier_v2'
            );
        });
    }

    public function down(): void
    {
        Schema::table('goal_tiers', function (Blueprint $table) {
            $table->dropForeign(['employee_local_id']);
            $table->dropUnique('unique_goal_tier_v2');
            $table->dropColumn('employee_local_id');
            $table->unique(
                ['owner_type', 'area', 'period_type', 'shift', 'sales_goal'],
                'unique_goal_tier'
            );
        });
    }
};
