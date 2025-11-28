<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleVehiculosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hardcode to user_id=1 for test user "Usuario Prueba"
        $userId = 1;

        // Sample vehicle data for the user
        $sampleData = [
            [
                'placas' => 'ABC123',
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'color' => 'Rojo',
                'nombre_cliente' => 'Usuario Prueba',
                'telefono' => '555-1234',
                'user_id' => $userId,
            ],
            [
                'placas' => 'DEF456',
                'marca' => 'Honda',
                'modelo' => 'Civic',
                'color' => 'Azul',
                'nombre_cliente' => 'Usuario Prueba',
                'telefono' => '555-5678',
                'user_id' => $userId,
            ],
            [
                'placas' => 'GHI789',
                'marca' => 'Ford',
                'modelo' => 'Focus',
                'color' => 'Negro',
                'nombre_cliente' => 'Usuario Prueba',
                'telefono' => '555-9012',
                'user_id' => $userId,
            ],
        ];

        // Insert the sample data
        DB::table('vehiculos_perfiles')->insert($sampleData);
    }
}
