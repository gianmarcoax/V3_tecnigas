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
        Schema::create('attendance_justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');                        // fecha de la falta/tardanza
            $table->enum('type', ['falta', 'tardanza']);
            $table->boolean('justified')->default(true); // true = justificada
            $table->text('reason')->nullable();          // motivo opcional
            $table->foreignId('created_by')             // qué usuario del dashboard lo registró
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['employee_id', 'date', 'type']); // una justif por empleado+día+tipo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_justifications');
    }
};
