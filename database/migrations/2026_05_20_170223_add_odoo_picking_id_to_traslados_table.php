<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_picking_id')->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->dropColumn('odoo_picking_id');
        });
    }
};
