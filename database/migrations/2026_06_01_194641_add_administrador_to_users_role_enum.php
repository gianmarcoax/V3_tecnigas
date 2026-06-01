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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'administrador'::character varying, 'almacen'::character varying, 'vendedor'::character varying, 'limpieza'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'almacen'::character varying, 'vendedor'::character varying, 'limpieza'::character varying]::text[]))");
    }
};
