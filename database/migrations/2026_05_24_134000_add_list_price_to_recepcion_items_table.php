<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion_items', function (Blueprint $table) {
            // Precio de venta de Odoo (list_price) — columna C en el XLSX de BarTender
            $table->decimal('list_price', 10, 2)->default(0)->after('costo');
        });
    }

    public function down(): void
    {
        Schema::table('recepcion_items', function (Blueprint $table) {
            $table->dropColumn('list_price');
        });
    }
};
