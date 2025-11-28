<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Espacio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ReservationConfirmedNotification;

class ReservationController extends Controller
{
    public function index(): JsonResponse
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with('espacio')
            ->orderBy('fecha_hora_reserva')
            ->get();

        return response()->json($reservations);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'espacio_id' => 'required|exists:espacios,id',
            'fecha_hora_reserva' => 'required|date|after:now',
            'duracion' => 'required|integer|min:15|max:480'
        ]);

        $espacio = Espacio::findOrFail($request->espacio_id);
        $startTime = \Carbon\Carbon::parse($request->fecha_hora_reserva);

        // Check if space is available at that time using the new method
        if (!$espacio->isAvailableForPeriod($startTime, $request->duracion)) {
            return response()->json(['message' => 'El espacio no está disponible en ese horario'], 409);
        }

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'espacio_id' => $request->espacio_id,
            'fecha_hora_reserva' => $request->fecha_hora_reserva,
            'duracion' => $request->duracion,
            'estado' => 'pendiente'
        ]);

        // Send confirmation notification
        Auth::user()->notify(new ReservationConfirmedNotification($reservation->load('espacio')));

        return response()->json($reservation, 201);
    }

    public function cancel($id): JsonResponse
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($reservation->estado !== 'pendiente') {
            return response()->json(['message' => 'No se puede cancelar esta reserva'], 409);
        }

        $reservation->estado = 'cancelada';
        $reservation->save();

        return response()->json(['message' => 'Reserva cancelada']);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_hora' => 'required|date',
            'duracion' => 'required|integer|min:15|max:480'
        ]);

        $espacios = Espacio::all();

        $available = $espacios->filter(function ($espacio) use ($request) {
            $conflict = Reservation::where('espacio_id', $espacio->id)
                ->where('estado', '!=', 'cancelada')
                ->where(function ($query) use ($request) {
                    $query->whereBetween('fecha_hora_reserva', [
                        $request->fecha_hora,
                        date('Y-m-d H:i:s', strtotime($request->fecha_hora) + $request->duracion * 60)
                    ])->orWhere(function ($q) use ($request) {
                        $q->where('fecha_hora_reserva', '<=', $request->fecha_hora)
                          ->whereRaw('DATE_ADD(fecha_hora_reserva, INTERVAL duracion MINUTE) > ?', [$request->fecha_hora]);
                    });
                })->exists();

            return !$conflict;
        })->map(function ($espacio) {
            return [
                'id' => $espacio->id,
                'codigo' => $espacio->codigo
            ];
        });

        return response()->json($available);
    }
}
