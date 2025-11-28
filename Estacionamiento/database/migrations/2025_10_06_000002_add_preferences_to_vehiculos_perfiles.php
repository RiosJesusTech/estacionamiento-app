<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreferencesToVehiculosPerfiles extends Migration
{
    public function up()
    {
        Schema::table('vehiculos_perfiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('telefono');
            $table->string('cajon_favorito')->nullable()->after('user_id');
            $table->string('horario_preferido')->nullable()->after('cajon_favorito');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('vehiculos_perfiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'cajon_favorito', 'horario_preferido']);
        });
    }
}
