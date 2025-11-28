<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccupancyLogsTable extends Migration
{
    public function up()
    {
        Schema::create('occupancy_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('espacio_id');
            $table->timestamp('fecha_hora');
            $table->enum('estado', ['disponible', 'ocupado']);
            $table->unsignedBigInteger('vehiculo_id')->nullable();
            $table->timestamps();

            $table->foreign('espacio_id')->references('id')->on('espacios')->onDelete('cascade');
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('occupancy_logs');
    }
}
