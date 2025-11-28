<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Espacio;
use App\Models\VehiculoPerfil;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Notifications\SpotAvailableNotification;
use App\Models\User;

class VehiculoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tipo'   => 'required|in:automovil,motocicleta',
            'marca'  => 'required|string',
            'modelo' => 'required|string',
            'placas' => 'required|string',
            'color'  => 'required|string',
            'seccion' => 'required|string|exists:espacios,codigo',
            'nombre_cliente' => 'nullable|string',
            'pertenencias'   => 'nullable|string',
            'telefono' => 'nullable|string'
        ]);

        $espacio = Espacio::where('codigo', $request->seccion)
                          ->where('estado', 'disponible')
                          ->first();

        if (!$espacio) {
            return response()->json(['message' => 'El espacio no está disponible'], 409);
        }

        // Check for active reservation for this space and current time
        $now = \Carbon\Carbon::now();
        $activeReservation = $espacio->reservations()
            ->where('estado', 'confirmada')
            ->where('fecha_hora_reserva', '<=', $now)
            ->whereRaw('DATE_ADD(fecha_hora_reserva, INTERVAL duracion MINUTE) > ?', [$now])
            ->first();

        if ($activeReservation) {
            // Fulfill the reservation
            $activeReservation->fulfill();
        }

        return DB::transaction(function () use ($request, $espacio) {
            $vehiculo = Vehiculo::create([
                'tipo'           => $request->tipo,
                'marca'          => $request->marca,
                'modelo'         => $request->modelo,
                'placas'         => $request->placas,
                'color'          => $request->color,
                'nombre_cliente' => $request->nombre_cliente,
                'pertenencias'   => $request->pertenencias,
                'telefono'       => $request->telefono,
                'espacio_id'     => $espacio->id,
            ]);

            // Crear o actualizar el perfil del vehículo
            VehiculoPerfil::updateOrCreate(
                ['placas' => $request->placas],
                [
                    'marca' => $request->marca,
                    'modelo' => $request->modelo,
                    'color' => $request->color,
                    'nombre_cliente' => $request->nombre_cliente,
                    'telefono' => $request->telefono,
                ]
            );

            $espacio->estado = 'ocupado';
            $espacio->save();

            return response()->json(['ticket_id' => $vehiculo->id], 201);
        });
    }

    public function estacionados(): JsonResponse
    {
        $vehiculos = Vehiculo::whereNull('hora_salida')
            ->with('espacio')
            ->get()
            ->map(function ($vehiculo) {
                return [
                    'id' => $vehiculo->id,
                    'tipo' => $vehiculo->tipo,
                    'marca' => $vehiculo->marca,
                    'modelo' => $vehiculo->modelo,
                    'placas' => $vehiculo->placas,
                    'color' => $vehiculo->color,
                    'nombre_cliente' => $vehiculo->nombre_cliente,
                    'pertenencias' => $vehiculo->pertenencias,
                    'telefono' => $vehiculo->telefono,
                    'fecha_entrada' => $vehiculo->hora_entrada,
                    'seccion' => $vehiculo->espacio->codigo,
                ];
            });

        return response()->json($vehiculos);
    }

    public function buscar(Request $request): JsonResponse
    {
        $placas = $request->query('placas');
        if (!$placas) {
            return response()->json([], 200);
        }

        $vehiculos = VehiculoPerfil::where('placas', 'like', '%' . $placas . '%')
            ->get()
            ->map(function ($perfil) {
                return [
                    'id' => $perfil->id,
                    'placas' => $perfil->placas,
                    'marca' => $perfil->marca,
                    'modelo' => $perfil->modelo,
                    'color' => $perfil->color,
                    'nombre_cliente' => $perfil->nombre_cliente,
                    'telefono' => $perfil->telefono,
                ];
            });

        return response()->json($vehiculos);
    }

    public function userVehicles(): JsonResponse
    {
        $userId = auth()->id();
        $vehicles = VehiculoPerfil::where('user_id', $userId)
            ->get()
            ->map(function ($perfil) {
                return [
                    'id' => $perfil->id,
                    'placas' => $perfil->placas,
                    'marca' => $perfil->marca,
                    'modelo' => $perfil->modelo,
                    'color' => $perfil->color,
                    'nombre_cliente' => $perfil->nombre_cliente,
                    'telefono' => $perfil->telefono,
                ];
            });

        return response()->json($vehicles);
    }

    public function salida(Request $request, $id): JsonResponse
    {
        $request->validate([
            'monto' => 'required|numeric|min:0'
        ]);

        $vehiculo = Vehiculo::findOrFail($id);

        if ($vehiculo->hora_salida) {
            return response()->json(['message' => 'El vehículo ya salió'], 409);
        }

        return DB::transaction(function () use ($request, $vehiculo) {
            $fechaSalida = now();
            $vehiculo->hora_salida = $fechaSalida;
            $vehiculo->save();

            $vehiculo->espacio->estado = 'disponible';
            $vehiculo->espacio->save();

            // Notify users who have this space as favorite or frequent customers
            $usersToNotify = User::whereHas('vehiculoPerfiles', function ($query) use ($vehiculo) {
                $query->where('cajon_favorito', $vehiculo->espacio->codigo);
            })->get();

            foreach ($usersToNotify as $user) {
                $user->notify(new SpotAvailableNotification($vehiculo->espacio));
            }

            // Crear la transacción
            $transaccion = Transaccion::create([
                'placas' => $vehiculo->placas,
                'fecha_entrada' => $vehiculo->hora_entrada,
                'fecha_salida' => $fechaSalida,
                'monto' => $request->monto
            ]);

            return response()->json([
                'message' => 'Salida registrada',
                'ticket_id' => $transaccion->id
            ]);
        });
    }
}
