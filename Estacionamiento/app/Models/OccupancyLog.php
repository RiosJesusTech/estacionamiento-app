<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OccupancyLog extends Model
{
    protected $fillable = [
        'espacio_id', 'fecha_hora', 'estado', 'vehiculo_id'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function espacio()
    {
        return $this->belongsTo(Espacio::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
