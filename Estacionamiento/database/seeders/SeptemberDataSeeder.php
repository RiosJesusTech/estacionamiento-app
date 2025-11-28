<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeptemberDataSeeder extends Seeder
{
    public function run()
    {
        // Seed additional September transactions for frequent users
        $septemberTransactions = [];
        $septemberStart = now()->setYear(2024)->setMonth(9)->setDay(1)->startOfDay();
        $septemberEnd = now()->setYear(2024)->setMonth(9)->setDay(30)->endOfDay();

        // Create frequent users data for September
        $frequentUsers = [
            ['placas' => 'ABC123', 'nombre' => 'Juan Pérez', 'visits' => 15],
            ['placas' => 'DEF456', 'nombre' => 'María García', 'visits' => 12],
            ['placas' => 'JKL012', 'nombre' => 'Ana Rodríguez', 'visits' => 10],
            ['placas' => 'PQR678', 'nombre' => 'Laura Sánchez', 'visits' => 8],
            ['placas' => 'VWX234', 'nombre' => 'Carmen Torres', 'visits' => 7],
        ];

        foreach ($frequentUsers as $user) {
            for ($i = 0; $i < $user['visits']; $i++) {
                $fechaEntrada = $septemberStart->copy()->addDays(rand(0, 29))->setTime(rand(6, 22), rand(0, 59));
                $fechaSalida = $fechaEntrada->copy()->addHours(rand(1, 8));
                $monto = rand(15, 80) + (rand(0, 99) / 100);

                $septemberTransactions[] = [
                    'placas' => $user['placas'],
                    'fecha_entrada' => $fechaEntrada,
                    'fecha_salida' => $fechaSalida,
                    'monto' => $monto,
                    'created_at' => $fechaSalida,
                    'updated_at' => $fechaSalida,
                ];
            }
        }
        DB::table('transacciones')->insert($septemberTransactions);

        // Seed additional September occupancy logs for better reporting
        $septemberLogs = [];
        for ($i = 1; $i <= 200; $i++) {
            $fechaHora = $septemberStart->copy()->addDays(rand(0, 29))->addHours(rand(6, 22));
            $septemberLogs[] = [
                'espacio_id' => rand(1, 30),
                'fecha_hora' => $fechaHora,
                'estado' => rand(0, 1) ? 'ocupado' : 'disponible',
                'vehiculo_id' => rand(0, 1) ? rand(1, 10) : null,
                'created_at' => $fechaHora,
                'updated_at' => $fechaHora,
            ];
        }
        DB::table('occupancy_logs')->insert($septemberLogs);

        // Seed packages for testing
        DB::table('packages')->insert([
            [
                'name' => 'Paquete Básico',
                'description' => 'Paquete básico con 5 reservas diarias',
                'price' => 50.00,
                'duration_days' => 30,
                'max_reservations_per_day' => 5,
                'fixed_spot' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paquete Premium',
                'description' => 'Paquete premium con 10 reservas diarias y espacio fijo',
                'price' => 100.00,
                'duration_days' => 30,
                'max_reservations_per_day' => 10,
                'fixed_spot' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paquete Empresarial',
                'description' => 'Paquete empresarial con reservas ilimitadas',
                'price' => 200.00,
                'duration_days' => 60,
                'max_reservations_per_day' => 20,
                'fixed_spot' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed user packages for testing
        DB::table('user_packages')->insert([
            [
                'user_id' => 6,
                'package_id' => 1,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(20)->toDateString(),
                'reservations_used_today' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'package_id' => 2,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(25)->toDateString(),
                'reservations_used_today' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
