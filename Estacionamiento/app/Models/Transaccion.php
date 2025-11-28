<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    protected $table = 'transacciones';

    protected $fillable = [
        'placas',
        'fecha_entrada',
        'fecha_salida',
        'monto',
        'user_id'
    ];

    protected $casts = [
        'fecha_entrada' => 'datetime',
        'fecha_salida' => 'datetime',
        'monto' => 'decimal:2'
    ];

    public function vehiculoPerfil()
    {
        return $this->belongsTo(VehiculoPerfil::class, 'placas', 'placas');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
