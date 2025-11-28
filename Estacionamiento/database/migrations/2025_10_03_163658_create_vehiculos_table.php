<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['automovil', 'motocicleta', 'otros']);
            $table->string('marca');
            $table->string('modelo');
            $table->string('placas');
            $table->string('color');
            $table->string('nombre_cliente')->nullable();
            $table->text('pertenencias')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamp('hora_entrada')->useCurrent();
            $table->timestamp('hora_salida')->nullable();
            $table->foreignId('espacio_id')->constrained('espacios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehiculos');
    }
}
