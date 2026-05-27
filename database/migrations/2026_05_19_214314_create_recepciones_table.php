<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->string('proveedor_nombre');
            $table->string('documento')->nullable();
            $table->string('usuario')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('location_dest_id')->nullable();  // ID ubicación destino en Odoo
            $table->unsignedBigInteger('odoo_picking_id')->nullable();   // ID del stock.picking en Odoo
            $table->timestamps();
        });

        Schema::create('recepcion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones')->cascadeOnDelete();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('producto_nombre');
            $table->decimal('cantidad', 12, 4)->default(0);
            $table->decimal('costo', 12, 4)->default(0);
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_items');
        Schema::dropIfExists('recepciones');
    }
};