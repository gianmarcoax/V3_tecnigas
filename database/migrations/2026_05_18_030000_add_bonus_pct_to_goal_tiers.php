<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goal_tiers', function (Blueprint $table) {
            // Porcentaje sobre ventas (0.100 = 0.1% de las ventas)
            // Si es null, se usa bonus_amount como monto fijo (retrocompat)
            $table->decimal('bonus_pct', 8, 4)->nullable()->after('bonus_amount');
        });
    }

    public function down(): void
    {
        Schema::table('goal_tiers', function (Blueprint $table) {
            $table->dropColumn('bonus_pct');
        });
    }
};
