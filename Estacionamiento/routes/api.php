<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PackageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/espacios', [EspacioController::class, 'index']);
    Route::get('/vehiculos/estacionados', [VehiculoController::class, 'estacionados']);
    Route::get('/vehiculos/buscar', [VehiculoController::class, 'buscar']);
    Route::get('/vehicles', [VehiculoController::class, 'userVehicles']);
    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::post('/vehiculos/{id}/salida', [VehiculoController::class, 'salida']);
    Route::get('/transacciones', [TransaccionController::class, 'index']);
    Route::get('/transacciones/my', [TransaccionController::class, 'myTransactions']);
    Route::post('/transacciones', [TransaccionController::class, 'store']);

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::post('/reservations/check-availability', [ReservationController::class, 'checkAvailability']);

    // Packages
    Route::get('/packages', [PackageController::class, 'index']);
    Route::post('/packages', [PackageController::class, 'store']);
    Route::get('/packages/my', [PackageController::class, 'myPackages']);
    Route::post('/packages/purchase', [PackageController::class, 'purchasePackage']);
    Route::get('/packages/suggestions', [PackageController::class, 'suggestPackages']);

    // Worker notifications
    Route::post('/notify/available-spots', [PackageController::class, 'notifyAvailableSpots']);
    Route::get('/notify/available-spots', [PackageController::class, 'getAvailableSpotsForNotification']);

    // Admin
    Route::get('/admin/sales-report', [AdminController::class, 'salesReport']);
    Route::get('/admin/occupancy-stats', [AdminController::class, 'occupancyStats']);
    Route::get('/admin/daily-reservations', [AdminController::class, 'dailyReservations']);
    Route::get('/admin/customer-analytics', [AdminController::class, 'customerAnalytics']);
    Route::get('/admin/monthly-review', [AdminController::class, 'monthlyReview']);
    Route::get('/admin/daily-report', [AdminController::class, 'dailyReport']);
    Route::get('/admin/monthly-report', [AdminController::class, 'monthlyReport']);
    Route::get('/admin/user-reservations', [AdminController::class, 'getUserReservations']);
    Route::get('/admin/active-user-packages', [AdminController::class, 'getActiveUserPackages']);
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-plate', [AuthController::class, 'loginByPlate']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->get('/check-auth', [AuthController::class, 'checkAuth']);



