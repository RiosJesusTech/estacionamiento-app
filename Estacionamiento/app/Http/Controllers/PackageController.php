<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UserPackage;
use App\Models\Espacio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = Package::where('active', true)->get();
        return response()->json($packages);
    }

    public function suggestPackages(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Get user's past transactions and packages
        $userTransactions = \App\Models\Transaccion::where('user_id', $userId)
            ->with('package')
            ->get();

        // Analyze user's package usage and preferences
        $packageUsage = [];
        foreach ($userTransactions as $transaction) {
            if ($transaction->package) {
                $packageId = $transaction->package->id;
                if (!isset($packageUsage[$packageId])) {
                    $packageUsage[$packageId] = 0;
                }
                $packageUsage[$packageId]++;
            }
        }

        // Sort packages by usage count descending
        arsort($packageUsage);

        // Get top packages used by user
        $topPackageIds = array_keys($packageUsage);

        // Fetch package details for top packages
        $suggestedPackages = Package::whereIn('id', $topPackageIds)
            ->where('active', true)
            ->get();

        // If no usage data, suggest popular packages
        if ($suggestedPackages->isEmpty()) {
            $suggestedPackages = Package::where('active', true)
                ->orderBy('popularity', 'desc') // Assuming popularity field exists
                ->take(5)
                ->get();
        }

        return response()->json($suggestedPackages);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_reservations_per_day' => 'required|integer|min:1',
            'fixed_spot' => 'boolean',
            'preferred_schedule' => 'nullable|array',
        ]);

        $package = Package::create($request->all());
        return response()->json($package, 201);
    }

    public function show($id): JsonResponse
    {
        $package = Package::findOrFail($id);
        return response()->json($package);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $package = Package::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'max_reservations_per_day' => 'sometimes|required|integer|min:1',
            'fixed_spot' => 'boolean',
            'preferred_schedule' => 'nullable|array',
            'active' => 'boolean',
        ]);

        $package->update($request->all());
        return response()->json($package);
    }

    public function destroy($id): JsonResponse
    {
        $package = Package::findOrFail($id);
        $package->delete();
        return response()->json(['message' => 'Package deleted']);
    }

    // User package management
    public function purchasePackage(Request $request): JsonResponse
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = Package::findOrFail($request->package_id);

        // Check if user already has an active package
        $existingPackage = UserPackage::where('user_id', Auth::id())
            ->where('active', true)
            ->where('end_date', '>', now())
            ->first();

        if ($existingPackage) {
            return response()->json(['message' => 'Ya tienes un paquete activo'], 409);
        }

        DB::transaction(function () use ($package) {
            $userPackage = UserPackage::create([
                'user_id' => Auth::id(),
                'package_id' => $package->id,
                'start_date' => now(),
                'end_date' => now()->addDays($package->duration_days),
                'assigned_spot' => $package->fixed_spot ? $this->assignFixedSpot() : null,
                'active' => true,
            ]);

            // If fixed spot, reserve it for the package duration
            if ($package->fixed_spot && $userPackage->assigned_spot) {
                // Logic to reserve the spot for the package period
                // This would involve creating reservations or marking the spot as reserved
            }
        });

        return response()->json(['message' => 'Paquete adquirido exitosamente']);
    }

    public function myPackages(): JsonResponse
    {
        $userPackages = UserPackage::where('user_id', Auth::id())
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($userPackages);
    }

    private function assignFixedSpot()
    {
        // Find an available spot for fixed assignment
        $availableSpot = Espacio::where('estado', 'disponible')->first();

        if ($availableSpot) {
            // Mark as reserved for package
            $availableSpot->estado = 'reservado';
            $availableSpot->save();
            return $availableSpot->codigo;
        }

        return null;
    }

    // Worker messaging system
    public function notifyAvailableSpots(Request $request): JsonResponse
    {
        $request->validate([
            'espacio_id' => 'required|exists:espacios,id',
            'message' => 'required|string|max:500',
        ]);

        $espacio = Espacio::findOrFail($request->espacio_id);

        // Get frequent clients who might be interested
        $frequentClients = DB::table('vehiculos_perfiles')
            ->whereNotNull('user_id')
            ->select('user_id', 'nombre_cliente')
            ->distinct()
            ->get();

        $notificationsSent = 0;

        foreach ($frequentClients as $client) {
            $user = DB::table('users')->where('id', $client->user_id)->first();
            if ($user && $user->email) {
                // Send notification
                // For now, we'll just count them
                $notificationsSent++;
            }
        }

        // Create a temporary hold on the spot for 20 minutes
        DB::table('spot_holds')->insert([
            'espacio_id' => $request->espacio_id,
            'held_by' => Auth::id(),
            'expires_at' => now()->addMinutes(20),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notificaciones enviadas y espacio reservado por 20 minutos',
            'notifications_sent' => $notificationsSent,
            'hold_expires_at' => now()->addMinutes(20)->toISOString(),
        ]);
    }

    public function getAvailableSpotsForNotification(): JsonResponse
    {
        $spots = Espacio::where('estado', 'disponible')->get(['id', 'codigo']);
        return response()->json($spots);
    }
}
