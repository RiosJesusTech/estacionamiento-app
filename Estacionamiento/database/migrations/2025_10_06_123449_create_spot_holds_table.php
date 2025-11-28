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
        Schema::create('spot_holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('espacio_id');
            $table->unsignedBigInteger('held_by'); // user id who holds the spot
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('espacio_id')->references('id')->on('espacios')->onDelete('cascade');
            $table->foreign('held_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_holds');
    }
};
