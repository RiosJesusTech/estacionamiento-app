<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed users with roles
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gestor User',
                'username' => 'gestor',
                'email' => 'gestor@example.com',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Normal User',
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trabajador Uno',
                'username' => 'trabajador1',
                'email' => 'trabajador1@example.com',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trabajador Dos',
                'username' => 'trabajador2',
                'email' => 'trabajador2@example.com',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cliente Frecuente',
                'username' => 'cliente1',
                'email' => 'cliente1@example.com',
                'password' => Hash::make('password'),
                'role' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cliente Nuevo',
                'username' => 'cliente2',
                'email' => 'cliente2@example.com',
                'password' => Hash::make('password'),
                'role' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed espacios (parking spaces) - 30 spaces
        $espacios = [];
        for ($i = 1; $i <= 30; $i++) {
            $espacios[] = [
                'codigo' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => $i <= 20 ? 'disponible' : 'ocupado', // 20 available, 10 occupied
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('espacios')->insert($espacios);

        // Seed vehiculos_perfiles with preferences
        DB::table('vehiculos_perfiles')->insert([
            [
                'placas' => 'ABC123',
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'color' => 'Rojo',
                'nombre_cliente' => 'cliente1',
                'telefono' => '555-1234',
                'user_id' => 6, // Cliente Frecuente
                'cajon_favorito' => 'A01',
                'horario_preferido' => '08:00-12:00',
            ],
            [
                'placas' => 'DEF456',
                'marca' => 'Honda',
                'modelo' => 'Civic',
                'color' => 'Azul',
                'nombre_cliente' => 'cliente1',
                'telefono' => '555-5678',
                'user_id' => 6,
                'cajon_favorito' => 'A02',
                'horario_preferido' => '14:00-18:00',
            ],
            [
                'placas' => 'GHI789',
                'marca' => 'Ford',
                'modelo' => 'Focus',
                'color' => 'Negro',
                'nombre_cliente' => 'cliente2',
                'telefono' => '555-9012',
                'user_id' => 7,
                'cajon_favorito' => 'A03',
                'horario_preferido' => '09:00-13:00',
            ],
            [
                'placas' => 'JKL012',
                'marca' => 'Chevrolet',
                'modelo' => 'Cruze',
                'color' => 'Blanco',
                'nombre_cliente' => 'cliente1',
                'telefono' => '555-3456',
                'user_id' => 6,
                'cajon_favorito' => 'A04',
                'horario_preferido' => '16:00-20:00',
            ],
            [
                'placas' => 'MNO345',
                'marca' => 'Nissan',
                'modelo' => 'Sentra',
                'color' => 'Gris',
                'nombre_cliente' => 'cliente2',
                'telefono' => '555-7890',
                'user_id' => 7,
                'cajon_favorito' => 'A05',
                'horario_preferido' => '10:00-14:00',
            ],
            [
                'placas' => 'PQR678',
                'marca' => 'BMW',
                'modelo' => 'Serie 3',
                'color' => 'Azul Marino',
                'nombre_cliente' => 'cliente1',
                'telefono' => '555-1234',
                'user_id' => 6,
                'cajon_favorito' => 'A06',
                'horario_preferido' => '07:00-11:00',
            ],
            [
                'placas' => 'STU901',
                'marca' => 'Mercedes',
                'modelo' => 'Clase C',
                'color' => 'Plateado',
                'nombre_cliente' => 'cliente2',
                'telefono' => '555-5678',
                'user_id' => 7,
                'cajon_favorito' => 'A07',
                'horario_preferido' => '12:00-16:00',
            ],
            [
                'placas' => 'VWX234',
                'marca' => 'Audi',
                'modelo' => 'A4',
                'color' => 'Rojo Oscuro',
                'nombre_cliente' => 'cliente1',
                'telefono' => '555-9012',
                'user_id' => 6,
                'cajon_favorito' => 'A08',
                'horario_preferido' => '15:00-19:00',
            ],
        ]);

        // Create map of placas to user_id from vehiculos_perfiles
        $placaToUserId = DB::table('vehiculos_perfiles')->pluck('user_id', 'placas')->toArray();

        // Seed vehiculos (currently parked) - 10 vehicles
        DB::table('vehiculos')->insert([
            [
                'tipo' => 'automovil',
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'placas' => 'ABC123',
                'color' => 'Rojo',
                'hora_entrada' => now()->subHours(3),
                'espacio_id' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Honda',
                'modelo' => 'Civic',
                'placas' => 'DEF456',
                'color' => 'Azul',
                'hora_entrada' => now()->subHours(2),
                'espacio_id' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Ford',
                'modelo' => 'Focus',
                'placas' => 'GHI789',
                'color' => 'Negro',
                'hora_entrada' => now()->subHours(1),
                'espacio_id' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Chevrolet',
                'modelo' => 'Cruze',
                'placas' => 'JKL012',
                'color' => 'Blanco',
                'hora_entrada' => now()->subHours(4),
                'espacio_id' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Nissan',
                'modelo' => 'Sentra',
                'placas' => 'MNO345',
                'color' => 'Gris',
                'hora_entrada' => now()->subHours(5),
                'espacio_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'BMW',
                'modelo' => 'Serie 3',
                'placas' => 'PQR678',
                'color' => 'Azul Marino',
                'hora_entrada' => now()->subHours(6),
                'espacio_id' => 26,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Mercedes',
                'modelo' => 'Clase C',
                'placas' => 'STU901',
                'color' => 'Plateado',
                'hora_entrada' => now()->subHours(7),
                'espacio_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Audi',
                'modelo' => 'A4',
                'placas' => 'VWX234',
                'color' => 'Rojo Oscuro',
                'hora_entrada' => now()->subHours(8),
                'espacio_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'motocicleta',
                'marca' => 'Yamaha',
                'modelo' => 'MT-07',
                'placas' => 'YZA567',
                'color' => 'Negro',
                'hora_entrada' => now()->subHours(2),
                'espacio_id' => 29,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'automovil',
                'marca' => 'Kia',
                'modelo' => 'Rio',
                'placas' => 'BCD890',
                'color' => 'Verde',
                'hora_entrada' => now()->subHours(3),
                'espacio_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed packages - 5 sample packages
        DB::table('packages')->insert([
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
            [
                'name' => 'Paquete Anual Premium',
                'description' => 'Acceso ilimitado por un año con beneficios extras',
                'price' => 5000.00,
                'duration_days' => 365,
                'max_reservations_per_day' => 20,
                'fixed_spot' => true,
                'preferred_schedule' => json_encode(['07:00-19:00']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paquete Familiar',
                'description' => 'Para múltiples vehículos',
                'price' => 2500.00,
                'duration_days' => 180,
                'max_reservations_per_day' => 15,
                'fixed_spot' => true,
                'preferred_schedule' => json_encode(['08:00-18:00']),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed user_packages for user_id=6 (Cliente Frecuente)
        DB::table('user_packages')->insert([
            [
                'user_id' => 6,
                'package_id' => 1, // Paquete Mensual Estándar
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
                'package_id' => 2, // Paquete Semanal
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
                'package_id' => 3, // Paquete Diario
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

        // Seed transacciones (transactions) - 25 transactions
        $transacciones = [];
        for ($i = 1; $i <= 25; $i++) {
            $fechaEntrada = now()->subDays(rand(0, 30))->setTime(rand(6, 22), rand(0, 59));
            $fechaSalida = $fechaEntrada->copy()->addHours(rand(1, 8));
            $monto = rand(10, 100) + (rand(0, 99) / 100);

            $placas = ['ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345', 'PQR678', 'STU901', 'VWX234'][rand(0, 7)];
            $transacciones[] = [
                'placas' => $placas,
                'user_id' => $placaToUserId[$placas],
                'fecha_entrada' => $fechaEntrada,
                'fecha_salida' => $fechaSalida,
                'monto' => $monto,
                'created_at' => $fechaSalida,
                'updated_at' => $fechaSalida,
            ];
        }
        DB::table('transacciones')->insert($transacciones);

        // Seed reservations - 8 reservations with some overlapping scenarios for testing
        DB::table('reservations')->insert([
            [
                'user_id' => 6,
                'espacio_id' => 1,
                'fecha_hora_reserva' => now()->addHours(2),
                'duracion' => 60,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'espacio_id' => 2,
                'fecha_hora_reserva' => now()->addHours(4),
                'duracion' => 120,
                'estado' => 'confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'espacio_id' => 3,
                'fecha_hora_reserva' => now()->addHours(6),
                'duracion' => 90,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'espacio_id' => 4,
                'fecha_hora_reserva' => now()->addHours(8),
                'duracion' => 60,
                'estado' => 'confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'espacio_id' => 5,
                'fecha_hora_reserva' => now()->addHours(10),
                'duracion' => 180,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'espacio_id' => 6,
                'fecha_hora_reserva' => now()->addHours(12),
                'duracion' => 60,
                'estado' => 'confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'espacio_id' => 7,
                'fecha_hora_reserva' => now()->addHours(14),
                'duracion' => 120,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'espacio_id' => 8,
                'fecha_hora_reserva' => now()->addHours(16),
                'duracion' => 90,
                'estado' => 'confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional reservations for testing conflicts
            [
                'user_id' => 6,
                'espacio_id' => 1, // Same space as first reservation, but different time
                'fecha_hora_reserva' => now()->addHours(24), // Next day
                'duracion' => 120,
                'estado' => 'confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'espacio_id' => 2, // Overlapping with second reservation
                'fecha_hora_reserva' => now()->addHours(5), // Overlaps with reservation 2
                'duracion' => 60,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed occupancy logs - 50 logs
        $logs = [];
        for ($i = 1; $i <= 50; $i++) {
            $logs[] = [
                'espacio_id' => rand(1, 30),
                'fecha_hora' => now()->subHours(rand(1, 168)), // Last 7 days
                'estado' => rand(0, 1) ? 'ocupado' : 'disponible',
                'vehiculo_id' => rand(0, 1) ? rand(1, 10) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('occupancy_logs')->insert($logs);
    }
}
