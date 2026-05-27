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
        Schema::table('recepcion_items', function (Blueprint $table) {
            $table->integer('tickets')->default(1)->after('cantidad');
            $table->string('default_code')->nullable()->after('producto_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepcion_items', function (Blueprint $table) {
            $table->dropColumn(['tickets', 'default_code']);
        });
    }
};
