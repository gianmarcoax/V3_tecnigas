<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_cleanliness_configs', function (Blueprint $table) {
            $table->id();
            // score_thresholds: [{from:0, to:0.5, points:0}, {from:0.5, to:1.5, points:1}, {from:1.5, to:2.0, points:2}]
            $table->json('score_thresholds');
            // discount_rules: [{points:0, discount_pct:100}, {points:1, discount_pct:50}, {points:2, discount_pct:0}]
            $table->json('discount_rules');
            $table->timestamps();
        });

        // Insertar registro por defecto
        DB::table('order_cleanliness_configs')->insert([
            'score_thresholds' => json_encode([
                ['from' => 0,   'to' => 0.5, 'points' => 0],
                ['from' => 0.5, 'to' => 1.5, 'points' => 1],
                ['from' => 1.5, 'to' => 2.0, 'points' => 2],
            ]),
            'discount_rules' => json_encode([
                ['points' => 0, 'discount_pct' => 100],
                ['points' => 1, 'discount_pct' => 50],
                ['points' => 2, 'discount_pct' => 0],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cleanliness_configs');
    }
};
