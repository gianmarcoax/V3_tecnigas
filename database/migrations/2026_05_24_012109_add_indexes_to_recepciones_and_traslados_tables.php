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
        Schema::table('recepciones', function (Blueprint $table) {
            $table->index('fecha');
            $table->index('proveedor_id');
            $table->index('odoo_picking_id');
        });

        Schema::table('traslados', function (Blueprint $table) {
            $table->index('fecha');
            $table->index('estado');
            $table->index('odoo_picking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepciones', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
            $table->dropIndex(['proveedor_id']);
            $table->dropIndex(['odoo_picking_id']);
        });

        Schema::table('traslados', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['odoo_picking_id']);
        });
    }
};
