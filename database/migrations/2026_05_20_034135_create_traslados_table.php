<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('almacen_origen_id')->nullable();
            $table->string('almacen_origen_nombre');
            $table->unsignedBigInteger('almacen_destino_id')->nullable();
            $table->string('almacen_destino_nombre');
            $table->string('usuario')->nullable();
            $table->enum('estado', ['pendiente','confirmado','cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->timestamps();
        });

        Schema::create('traslado_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')->constrained('traslados')->cascadeOnDelete();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('producto_nombre');
            $table->decimal('cantidad', 12, 4)->default(0);
            $table->string('unidad')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_items');
        Schema::dropIfExists('traslados');
    }
};