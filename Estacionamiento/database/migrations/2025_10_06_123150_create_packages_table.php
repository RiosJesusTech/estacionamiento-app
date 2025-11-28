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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->integer('duration_days'); // Duración del paquete en días
            $table->integer('max_reservations_per_day'); // Máximo de reservaciones por día
            $table->boolean('fixed_spot')->default(false); // Si incluye lugar fijo
            $table->json('preferred_schedule')->nullable(); // Horario preferido (ej: ["08:00-12:00", "14:00-18:00"])
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
