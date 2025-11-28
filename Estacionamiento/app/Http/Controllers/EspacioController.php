<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use Illuminate\Http\JsonResponse;

class EspacioController extends Controller
{
    public function index(): JsonResponse
    {
        // Cargar espacios junto con vehículos activos (sin hora_salida)
        $espacios = Espacio::with(['vehiculos' => function ($query) {
            $query->whereNull('hora_salida');
        }])->orderBy('codigo')->get(['id', 'codigo']);

        // Mapear y calcular estado dinámicamente
        $resultado = $espacios->map(function ($espacio) {
            return [
                'id' => $espacio->id,
                'codigo' => $espacio->codigo,
                'estado' => $espacio->vehiculos->isEmpty() ? 'disponible' : 'ocupado',
            ];
        });

        return response()->json($resultado);
    }
}
