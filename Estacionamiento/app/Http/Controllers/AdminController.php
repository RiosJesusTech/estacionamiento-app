<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use App\Models\Vehiculo;
use App\Models\VehiculoPerfil;
use App\Models\Espacio;
use App\Models\OccupancyLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function salesReport(Request $request): JsonResponse
    {
        if ($request->has('date')) {
            // Daily report
            $date = $request->query('date', Carbon::today());
            $start = Carbon::parse($date)->startOfDay();
            $end = Carbon::parse($date)->endOfDay();
        } else {
            // Monthly or custom range
            $start = $request->query('start', Carbon::now()->startOfMonth());
            $end = $request->query('end', Carbon::now()->endOfMonth());
        }

        $sales = Transaccion::whereBetween('fecha_salida', [$start, $end])
            ->selectRaw('SUM(ABS(monto)) as total_sales, COUNT(*) as total_transactions')
            ->first();

        return response()->json([
            'total_sales' => $sales->total_sales ?? 0,
            'total_transactions' => $sales->total_transactions ?? 0,
            'period' => [$start, $end]
        ]);
    }

    public function occupancyStats(Request $request): JsonResponse
    {
        if ($request->has('start') && $request->has('end')) {
            // Date range mode
            $start = Carbon::parse($request->query('start'));
            $end = Carbon::parse($request->query('end'));
            $isRange = true;
        } else {
            // Single date mode
            $date = $request->query('date', Carbon::today());
            $start = Carbon::parse($date)->startOfDay();
            $end = Carbon::parse($date)->endOfDay();
            $isRange = false;
        }

        $logs = OccupancyLog::whereBetween('fecha_hora', [$start, $end])
            ->with('espacio')
            ->get()
            ->groupBy('espacio_id');

        $stats = [];
        $hourlyOccupancy = [];
        foreach ($logs as $espacioId => $espacioLogs) {
            $occupiedTime = 0;
            $previousTime = null;
            $previousState = null;

            foreach ($espacioLogs->sortBy('fecha_hora') as $log) {
                if ($previousState === 'ocupado' && $previousTime) {
                    $occupiedTime += $log->fecha_hora->diffInMinutes($previousTime);
                }
                $previousTime = $log->fecha_hora;
                $previousState = $log->estado;

                // Track hourly occupancy
                $hour = $log->fecha_hora->hour;
                if (!isset($hourlyOccupancy[$hour])) {
                    $hourlyOccupancy[$hour] = 0;
                }
                if ($log->estado === 'ocupado') {
                    $hourlyOccupancy[$hour]++;
                }
            }

            // For range, utilization rate is over the total period
            $totalMinutes = $start->diffInMinutes($end);
            $utilizationRate = $totalMinutes > 0 ? round(($occupiedTime / $totalMinutes) * 100, 2) : 0;

            // Count active reservations for this espacio in the period
            $activeReservationsCount = \App\Models\Reservation::where('espacio_id', $espacioId)
                ->where('estado', 'confirmada')
                ->whereBetween('fecha_hora_reserva', [$start, $end])
                ->count();

            $stats[] = [
                'espacio' => $espacioLogs->first()->espacio->codigo,
                'occupied_minutes' => $occupiedTime,
                'active_reservations' => $activeReservationsCount,
                'utilization_rate' => $utilizationRate
            ];
        }

        $averageOccupancy = count($stats) > 0 ? round(array_sum(array_column($stats, 'utilization_rate')) / count($stats), 2) : 0;
        $peakHour = !empty($hourlyOccupancy) ? array_keys($hourlyOccupancy, max($hourlyOccupancy))[0] : 'N/A';

        // Calculate peak entry hours based on vehicle entries
        $peakEntryHours = Transaccion::whereBetween('fecha_entrada', [$start, $end])
            ->selectRaw('HOUR(fecha_entrada) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->get();

        $maxCount = $peakEntryHours->first()?->count ?? 0;
        $peakHoursList = $peakEntryHours->where('count', $maxCount)->pluck('hour')->sort();

        if ($peakHoursList->isEmpty()) {
            $peakEntryRange = 'N/A';
            $peakEntriesCount = 0;
        } else {
            $min = $peakHoursList->min();
            $max = $peakHoursList->max();
            $peakEntryRange = $min . '-' . $max;
            $peakEntriesCount = Transaccion::whereBetween('fecha_entrada', [$start, $end])
                ->whereIn(\DB::raw('HOUR(fecha_entrada)'), $peakHoursList)
                ->count();
        }

        return response()->json([
            'occupancy_logs' => $stats,
            'average_occupancy' => $averageOccupancy,
            'peak_hours' => $peakHour,
            'peak_entry_hours' => $peakEntryRange,
            'peak_entry_count' => $peakEntriesCount
        ]);
    }

    public function dailyReservations(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today());

        $reservationsCount = \App\Models\Reservation::whereDate('fecha_hora_reserva', $date)
            ->where('estado', '!=', 'cancelada')
            ->count();

        return response()->json($reservationsCount);
    }

    public function customerAnalytics(Request $request): JsonResponse
    {
        $start = $request->query('start', Carbon::now()->startOfMonth());
        $end = $request->query('end', Carbon::now()->endOfMonth());

        $frequentCustomers = VehiculoPerfil::withCount(['transacciones' => function ($query) use ($start, $end) {
            $query->whereBetween('fecha_entrada', [$start, $end]);
        }])
            ->orderBy('transacciones_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($perfil) {
                return [
                    'placas' => $perfil->placas,
                    'nombre_cliente' => $perfil->nombre_cliente,
                    'telefono' => $perfil->telefono,
                    'visits' => $perfil->transacciones_count,
                    'cajon_favorito' => $perfil->cajon_favorito,
                    'horario_preferido' => $perfil->horario_preferido
                ];
            });

        return response()->json($frequentCustomers);
    }

    public function monthlyReview(Request $request): JsonResponse
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        // If it's the current month, limit to current day
        $now = Carbon::now();
        if ($year == $now->year && $month == $now->month) {
            $end = $now->endOfDay();
        }

        // Peak hours analysis
        $peakHours = Transaccion::whereBetween('fecha_entrada', [$start, $end])
            ->selectRaw('HOUR(fecha_entrada) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->get();

        // Monthly occupancy
        $totalSpaces = Espacio::count();
        $occupiedLogs = OccupancyLog::whereBetween('fecha_hora', [$start, $end])
            ->where('estado', 'ocupado')
            ->count();

        $totalHours = $start->diffInHours($end) * $totalSpaces;
        $averageOccupancy = $totalHours > 0 ? round(($occupiedLogs / $totalHours) * 100, 2) : 0;

        // Reservations
        $reservationCount = \App\Models\Reservation::whereBetween('fecha_hora_reserva', [$start, $end])->count();
        $cancelledReservations = \App\Models\Reservation::whereBetween('fecha_hora_reserva', [$start, $end])
            ->where('estado', 'cancelada')
            ->count();
        $reservationRate = $reservationCount > 0 ? round((($reservationCount - $cancelledReservations) / $reservationCount) * 100, 2) : 0;

        // Revenue
        $revenue = Transaccion::whereBetween('fecha_salida', [$start, $end])
            ->sum('monto');

        return response()->json([
            'month' => $month,
            'year' => $year,
            'peak_hours' => $peakHours->first()?->hour ?? 'N/A',
            'average_occupancy' => $averageOccupancy,
            'reservation_count' => $reservationCount,
            'cancelled_reservations' => $cancelledReservations,
            'reservation_rate' => $reservationRate,
            'total_revenue' => $revenue,
            'total_spaces' => $totalSpaces
        ]);
    }

    public function dailyReport(Request $request)
    {
        $date = $request->query('date', Carbon::today());

        $sales = Transaccion::whereDate('fecha_salida', $date)
            ->selectRaw('SUM(monto) as total_sales, COUNT(*) as total_transactions')
            ->first();

        $occupancyStats = $this->occupancyStats($request)->getData();

        $pdf = Pdf::loadView('reports.daily', [
            'date' => $date,
            'sales' => $sales,
            'occupancyStats' => $occupancyStats,
        ]);

        return $pdf->download('reporte_diario_' . $date . '.pdf');
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        // If it's the current month, limit to current day
        $now = Carbon::now();
        if ($year == $now->year && $month == $now->month) {
            $end = $now->endOfDay();
        }

        $sales = Transaccion::whereBetween('fecha_salida', [$start, $end])
            ->selectRaw('SUM(monto) as total_sales, COUNT(*) as total_transactions')
            ->first();

        // Pass date range to occupancyStats method explicitly
        $occupancyRequest = new Request([
            'start' => $start->toDateString(),
            'end' => $end->toDateString()
        ]);
        $occupancyStats = $this->occupancyStats($occupancyRequest)->getData();

        $monthlyReview = $this->monthlyReview(new Request(['month' => $month, 'year' => $year]))->getData();

        $pdf = Pdf::loadView('reports.monthly', [
            'month' => $month,
            'year' => $year,
            'sales' => $sales,
            'occupancyStats' => $occupancyStats,
            'monthlyReview' => $monthlyReview,
        ]);

        return $pdf->download('reporte_mensual_' . $year . '_' . $month . '.pdf');
    }

    public function sendPackageNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'message' => 'required|string|max:500',
        ]);

        $package = \App\Models\Package::findOrFail($request->package_id);

        // Get frequent clients (users with more than 5 visits in the last month)
        $frequentClients = VehiculoPerfil::withCount(['transacciones' => function ($query) {
            $query->whereBetween('fecha_entrada', [Carbon::now()->subMonth(), Carbon::now()]);
        }])
            ->having('transacciones_count', '>', 5)
            ->whereNotNull('user_id')
            ->get();

        $notificationsSent = 0;

        foreach ($frequentClients as $client) {
            $user = User::find($client->user_id);
            if ($user && $user->email) {
                // Send notification (using Laravel notifications)
                $user->notify(new \App\Notifications\PackagePromotionNotification($package, $request->message));
                $notificationsSent++;
            }
        }

        return response()->json([
            'message' => 'Notificaciones enviadas a clientes frecuentes',
            'notifications_sent' => $notificationsSent,
            'package' => $package->name,
        ]);
    }

    // User management methods
    public function listUsers(Request $request): JsonResponse
    {
        $role = $request->query('role');
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    public function deleteUser($id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deleting admin users or the current user
        if ($user->role === 'admin' || $user->id === auth()->id()) {
            return response()->json(['message' => 'No se puede eliminar este usuario'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function updateUserRole(Request $request, $id): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:cliente,gestor,admin',
        ]);

        $user = User::findOrFail($id);

        // Prevent changing admin roles or the current user's role
        if ($user->role === 'admin' || $user->id === auth()->id()) {
            return response()->json(['message' => 'No se puede cambiar el rol de este usuario'], 403);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Rol actualizado exitosamente']);
    }

    public function getUserReservations(Request $request): JsonResponse
    {
        // Check if user is admin or gestor
        $currentUser = auth()->user();
        if (!in_array($currentUser->role, ['admin', 'gestor'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $users = User::where('role', 'user')
            ->with(['reservations' => function ($query) {
                $query->active()->with('espacio');
            }])
            ->whereHas('reservations', function ($query) {
                $query->active();
            })
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'reservations' => $user->reservations->map(function ($reservation) {
                        return [
                            'id' => $reservation->id,
                            'espacio_codigo' => $reservation->espacio->codigo ?? 'N/A',
                            'fecha_hora_reserva' => $reservation->fecha_hora_reserva,
                            'duracion' => $reservation->duracion,
                            'estado' => $reservation->estado,
                        ];
                    }),
                ];
            });

        return response()->json($users);
    }

    public function getActiveUserPackages(Request $request): JsonResponse
    {
        // Check if user is admin or gestor
        $currentUser = auth()->user();
        if (!in_array($currentUser->role, ['admin', 'gestor'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $users = User::where('role', 'user')
            ->with(['userPackages' => function ($query) {
                $query->where('active', true)->with('package');
            }])
            ->whereHas('userPackages', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'packages' => $user->userPackages->map(function ($userPackage) {
                        return [
                            'id' => $userPackage->id,
                            'package_name' => $userPackage->package->name ?? 'N/A',
                            'description' => $userPackage->package->description ?? 'N/A',
                            'price' => $userPackage->package->price ?? 0,
                            'duration_days' => $userPackage->package->duration_days ?? 0,
                            'start_date' => $userPackage->start_date,
                            'end_date' => $userPackage->end_date,
                            'assigned_spot' => $userPackage->assigned_spot,
                        ];
                    }),
                ];
            });

        return response()->json($users);
    }
}
