<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate user_id for existing transactions based on vehiculos_perfiles
        DB::statement('
            UPDATE transacciones
            SET user_id = (
                SELECT vp.user_id
                FROM vehiculos_perfiles vp
                WHERE vp.placas = transacciones.placas
                LIMIT 1
            )
            WHERE user_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, set user_id back to null, but since it's nullable, maybe not needed
        // DB::statement('UPDATE transacciones SET user_id = NULL');
    }
};
