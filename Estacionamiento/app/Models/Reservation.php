<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    protected $fillable = [
        'user_id', 'espacio_id', 'fecha_hora_reserva', 'duracion', 'estado'
    ];

    protected $casts = [
        'fecha_hora_reserva' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class);
    }

    /**
     * Scope for active reservations (confirmed and not yet completed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('estado', ['confirmada', 'pendiente']);
    }

    /**
     * Check if reservation is currently active (time-wise)
     */
    public function isCurrentlyActive()
    {
        $now = Carbon::now();
        $endTime = $this->fecha_hora_reserva->copy()->addMinutes($this->duracion);

        return $this->estado === 'confirmada' &&
               $now->between($this->fecha_hora_reserva, $endTime);
    }

    /**
     * Check if reservation conflicts with given time period
     */
    public function conflictsWith($startTime, $durationMinutes)
    {
        if (!in_array($this->estado, ['confirmada', 'pendiente'])) {
            return false;
        }

        $reservationEnd = $this->fecha_hora_reserva->copy()->addMinutes($this->duracion);
        $checkEnd = $startTime->copy()->addMinutes($durationMinutes);

        return $this->fecha_hora_reserva->lt($checkEnd) && $reservationEnd->gt($startTime);
    }

    /**
     * Get reservation end time
     */
    public function getEndTime()
    {
        return $this->fecha_hora_reserva->copy()->addMinutes($this->duracion);
    }

    /**
     * Mark reservation as fulfilled (when vehicle enters)
     */
    public function fulfill()
    {
        $this->estado = 'completada';
        $this->save();
    }
}
