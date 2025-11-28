<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoPerfil extends Model
{
    protected $table = 'vehiculos_perfiles';

    protected $fillable = [
        'placas', 'marca', 'modelo', 'color', 'nombre_cliente', 'telefono', 'user_id', 'cajon_favorito', 'horario_preferido'
    ];

    public $timestamps = false;

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'placas', 'placas');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
