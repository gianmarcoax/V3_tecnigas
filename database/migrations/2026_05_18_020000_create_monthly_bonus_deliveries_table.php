<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_bonus_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_local_id');
            $table->foreign('employee_local_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('week_start');          // Lunes de la semana
            $table->decimal('bonus_amount', 10, 2)->default(0); // Bono ganado esa semana
            $table->boolean('delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('delivered_by')->nullable();
            $table->foreign('delivered_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->unique(['employee_local_id', 'week_start'], 'unique_delivery');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_bonus_deliveries');
    }
};
