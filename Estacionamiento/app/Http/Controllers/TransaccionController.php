<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use App\Models\VehiculoPerfil;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransaccionController extends Controller
{
    public function index()
    {
        try {
            // Get all transactions with vehiculo perfil
            $transacciones = Transaccion::with('vehiculoPerfil')
                ->orderBy('fecha_salida', 'desc')
                ->get();

            return response()->json($transacciones);
        } catch (\Exception $e) {
            \Log::error('Error in TransaccionController index: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function myTransactions()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $transacciones = Transaccion::where('user_id', $user->id)
                ->with('vehiculoPerfil')
                ->orderBy('fecha_salida', 'desc')
                ->get()
                ->map(function ($tx) {
                    $tx->hora_entrada = $tx->fecha_entrada ? $tx->fecha_entrada->format('H:i:s') : null;
                    $tx->hora_salida = $tx->fecha_salida ? $tx->fecha_salida->format('H:i:s') : null;
                    return $tx;
                });

            return response()->json($transacciones);
        } catch (\Exception $e) {
            \Log::error('Error in TransaccionController myTransactions: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'placas' => 'required|string|max:15',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date',
            'monto' => 'required|numeric|min:0'
        ]);

        // Find the user_id from VehiculoPerfil
        $vehiculoPerfil = VehiculoPerfil::where('placas', $request->placas)->first();
        if (!$vehiculoPerfil) {
            return response()->json(['error' => 'Vehículo no encontrado'], 404);
        }

        $data = $request->all();
        $data['user_id'] = $vehiculoPerfil->user_id;

        $transaccion = Transaccion::create($data);

        return response()->json($transaccion, 201);
    }
}
