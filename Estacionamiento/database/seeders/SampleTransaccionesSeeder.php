<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleTransaccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample transaction data for user_id=1 (Usuario Prueba)
        $sampleData1 = [
            [
                'user_id' => 1,
                'placas' => 'ABC123',
                'fecha_entrada' => Carbon::now()->subDays(5)->setTime(9, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(5)->setTime(17, 0, 0),
                'monto' => 150.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'placas' => 'DEF456',
                'fecha_entrada' => Carbon::now()->subDays(3)->setTime(10, 30, 0),
                'fecha_salida' => Carbon::now()->subDays(3)->setTime(18, 30, 0),
                'monto' => 200.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'placas' => 'GHI789',
                'fecha_entrada' => Carbon::now()->subDay()->setTime(8, 15, 0),
                'fecha_salida' => null, // Pending
                'monto' => 0.00, // Will be calculated on exit
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Sample transaction data for user_id=6 (Cliente Frecuente - cliente1)
        $sampleData6 = [
            [
                'user_id' => 6,
                'placas' => 'ABC123', // Toyota Corolla Rojo
                'fecha_entrada' => Carbon::now()->subDays(7)->setTime(8, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(7)->setTime(16, 0, 0),
                'monto' => 120.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'ABC123',
                'fecha_entrada' => Carbon::now()->subDays(4)->setTime(9, 30, 0),
                'fecha_salida' => Carbon::now()->subDays(4)->setTime(17, 30, 0),
                'monto' => 140.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'DEF456', // Honda Civic Azul
                'fecha_entrada' => Carbon::now()->subDays(6)->setTime(10, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(6)->setTime(18, 0, 0),
                'monto' => 160.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'JKL012', // Chevrolet Cruze Blanco
                'fecha_entrada' => Carbon::now()->subDays(5)->setTime(11, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(5)->setTime(19, 0, 0),
                'monto' => 180.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'JKL012',
                'fecha_entrada' => Carbon::now()->subDays(2)->setTime(12, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(2)->setTime(20, 0, 0),
                'monto' => 190.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'PQR678', // BMW Serie 3 Azul Marino
                'fecha_entrada' => Carbon::now()->subDays(3)->setTime(7, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(3)->setTime(15, 0, 0),
                'monto' => 200.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 6,
                'placas' => 'VWX234', // Audi A4 Rojo Oscuro
                'fecha_entrada' => Carbon::now()->subDay()->setTime(13, 0, 0),
                'fecha_salida' => Carbon::now()->subDay()->setTime(21, 0, 0),
                'monto' => 210.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Sample transaction data for user_id=7 (Cliente Nuevo - cliente2)
        $sampleData7 = [
            [
                'user_id' => 7,
                'placas' => 'GHI789', // Ford Focus Negro
                'fecha_entrada' => Carbon::now()->subDays(8)->setTime(9, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(8)->setTime(17, 0, 0),
                'monto' => 130.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 7,
                'placas' => 'MNO345', // Nissan Sentra Gris
                'fecha_entrada' => Carbon::now()->subDays(6)->setTime(11, 0, 0),
                'fecha_salida' => Carbon::now()->subDays(6)->setTime(19, 0, 0),
                'monto' => 170.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 7,
                'placas' => 'MNO345',
                'fecha_entrada' => Carbon::now()->subDays(3)->setTime(10, 30, 0),
                'fecha_salida' => Carbon::now()->subDays(3)->setTime(18, 30, 0),
                'monto' => 175.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 7,
                'placas' => 'STU901', // Mercedes Clase C Plateado
                'fecha_entrada' => Carbon::now()->subDays(4)->setTime(8, 30, 0),
                'fecha_salida' => Carbon::now()->subDays(4)->setTime(16, 30, 0),
                'monto' => 220.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert the sample data
        DB::table('transacciones')->insert(array_merge($sampleData1, $sampleData6, $sampleData7));
    }
}
