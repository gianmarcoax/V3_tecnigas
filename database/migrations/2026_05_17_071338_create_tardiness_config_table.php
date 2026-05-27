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
    Schema::create('tardiness_config', function (Blueprint $table) {
        $table->id();
        $table->integer('threshold_minutes')->default(10);         // tolerancia en minutos
        $table->decimal('deduction_amount', 8, 2)->nullable();    // descuento fijo por tardanza (null = no aplica aún)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tardiness_config');
    }
};
