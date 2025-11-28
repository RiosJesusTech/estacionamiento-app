<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Espacio extends Model
{
    protected $fillable = ['codigo', 'estado'];
    public $timestamps = true;

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Check if the space is available for a given time period considering reservations
     */
    public function isAvailableForPeriod(Carbon $startTime, int $durationMinutes)
    {
        // Check if space is marked as available
        if ($this->estado !== 'disponible') {
            return false;
        }

        // Check for conflicting reservations
        $conflictingReservations = $this->reservations()
            ->whereIn('estado', ['confirmada', 'pendiente'])
            ->get()
            ->filter(function ($reservation) use ($startTime, $durationMinutes) {
                return $reservation->conflictsWith($startTime, $durationMinutes);
            });

        return $conflictingReservations->isEmpty();
    }
}
