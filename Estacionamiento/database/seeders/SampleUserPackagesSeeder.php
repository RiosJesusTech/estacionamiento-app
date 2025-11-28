<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleUserPackagesSeeder extends Seeder
{
    public function run()
    {
        // First, ensure packages exist (insert if not, but check to avoid duplicates)
        $packages = [
            [
                'name' => 'Paquete Mensual Estándar',
                'description' => 'Acceso ilimitado por un mes',
                'price' => 500.00,
                'duration_days' => 30,
                'max_reservations_per_day' => 10,
                'fixed_spot' => false,
                'preferred_schedule' => json_encode(['08:00-12:00', '14:00-18:00']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paquete Semanal',
                'description' => 'Acceso por una semana',
                'price' => 150.00,
                'duration_days' => 7,
                'max_reservations_per_day' => 5,
                'fixed_spot' => false,
                'preferred_schedule' => json_encode(['09:00-13:00']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paquete Diario',
                'description' => 'Acceso por un día',
                'price' => 25.00,
                'duration_days' => 1,
                'max_reservations_per_day' => 2,
                'fixed_spot' => false,
                'preferred_schedule' => json_encode(['10:00-14:00']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($packages as $package) {
            if (!DB::table('packages')->where('name', $package['name'])->exists()) {
                DB::table('packages')->insert($package);
            }
        }

        // Get package IDs
        $packageIds = DB::table('packages')->whereIn('name', ['Paquete Mensual Estándar', 'Paquete Semanal', 'Paquete Diario'])->pluck('id')->toArray();

        // Assume users with IDs 6 and 7 exist (Cliente Frecuente and Cliente Nuevo)
        // Insert user_packages
        DB::table('user_packages')->insert([
            [
                'user_id' => 6,
                'package_id' => $packageIds[0], // Paquete Mensual Estándar
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(15),
                'assigned_spot' => 'A01',
                'reservations_used_today' => 0,
                'last_reservation_date' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'package_id' => $packageIds[1], // Paquete Semanal
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(2),
                'assigned_spot' => 'A02',
                'reservations_used_today' => 1,
                'last_reservation_date' => now()->subDays(1),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'package_id' => $packageIds[2], // Paquete Diario
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(1),
                'assigned_spot' => 'A03',
                'reservations_used_today' => 0,
                'last_reservation_date' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
