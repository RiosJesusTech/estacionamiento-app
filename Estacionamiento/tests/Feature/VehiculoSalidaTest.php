<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Espacio;
use App\Models\Transaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class VehiculoSalidaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_creates_transaction_when_vehicle_exits()
    {
        // Crear un espacio disponible
        $espacio = Espacio::create([
            'codigo' => 'A-1',
            'estado' => 'disponible'
        ]);

        // Crear un vehículo estacionado
        $vehiculo = Vehiculo::create([
            'tipo' => 'automovil',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'placas' => 'ABC123',
            'color' => 'Rojo',
            'espacio_id' => $espacio->id,
            'hora_entrada' => now()->subHours(2)
        ]);

        // Cambiar el estado del espacio a ocupado
        $espacio->update(['estado' => 'ocupado']);

        // Verificar que no hay transacciones inicialmente
        $this->assertEquals(0, Transaccion::count());

        // Procesar la salida con un monto
        $response = $this->postJson("/api/vehiculos/{$vehiculo->id}/salida", [
            'monto' => 40.00
        ]);

        // Verificar respuesta exitosa
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Salida registrada'
                ]);

        // Verificar que el vehículo tiene hora_salida
        $vehiculo->refresh();
        $this->assertNotNull($vehiculo->hora_salida);

        // Verificar que el espacio está disponible
        $espacio->refresh();
        $this->assertEquals('disponible', $espacio->estado);

        // Verificar que se creó la transacción
        $this->assertEquals(1, Transaccion::count());
        $transaccion = Transaccion::first();
        $this->assertEquals('ABC123', $transaccion->placas);
        $this->assertEquals(40.00, $transaccion->monto);
        $this->assertEquals($vehiculo->hora_entrada, $transaccion->fecha_entrada);
        $this->assertNotNull($transaccion->fecha_salida);
    }

    /** @test */
    public function it_validates_monto_is_required()
    {
        $espacio = Espacio::create(['codigo' => 'A-1', 'estado' => 'disponible']);
        $vehiculo = Vehiculo::create([
            'tipo' => 'automovil',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'placas' => 'ABC123',
            'color' => 'Rojo',
            'espacio_id' => $espacio->id
        ]);

        $response = $this->postJson("/api/vehiculos/{$vehiculo->id}/salida", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['monto']);
    }

    /** @test */
    public function it_prevents_double_exit()
    {
        $espacio = Espacio::create(['codigo' => 'A-1', 'estado' => 'disponible']);
        $vehiculo = Vehiculo::create([
            'tipo' => 'automovil',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'placas' => 'ABC123',
            'color' => 'Rojo',
            'espacio_id' => $espacio->id,
            'hora_salida' => now()
        ]);

        $response = $this->postJson("/api/vehiculos/{$vehiculo->id}/salida", [
            'monto' => 20.00
        ]);

        $response->assertStatus(409)
                ->assertJson(['message' => 'El vehículo ya salió']);
    }
}
