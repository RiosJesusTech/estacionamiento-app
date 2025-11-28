<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $fillable = [
        'tipo', 'marca', 'modelo', 'placas', 'color',
        'nombre_cliente', 'pertenencias', 'espacio_id', 'telefono',
        'hora_entrada', 'hora_salida'
    ];

    public $timestamps = false;

    protected $dates = ['hora_entrada', 'hora_salida'];

    public function espacio()
    {
        return $this->belongsTo(Espacio::class);
    }
}
