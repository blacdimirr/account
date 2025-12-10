<?php

namespace Tests\Feature;

use App\Http\Controllers\NcfSequenceController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NcfSequenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario simulado y autenticar
        $user = User::factory()->create();
        Auth::login($user);

        // Mockear creatorId() para devolver el id del usuario
        Auth::user()->creatorId = fn() => $user->id;
    }

    /** @test */
    public function valida_payload_correcto()
    {
        $controller = new NcfSequenceController();

        $request = Request::create('/ncf-sequences', 'POST', [
            'ncf_type_id'   => 1,
            'serie'         => 'A',
            'start_number'  => 1,
            'end_number'    => 100,
            'current_number'=> 0,
            'valid_from'    => '2025-01-01',
            'valid_until'   => '2025-12-31',
            'is_active'     => true,
        ]);

        $payload = $controller->validatePayload($request);

        $this->assertEquals(1, $payload['ncf_type_id']);
        $this->assertEquals('A', $payload['serie']);
        $this->assertEquals(1, $payload['start_number']);
        $this->assertEquals(100, $payload['end_number']);
        $this->assertEquals(0, $payload['current_number']);
        $this->assertTrue($payload['is_active']);
    }

    /** @test */
    public function falla_si_start_number_es_mayor_que_end_number()
    {
        $controller = new NcfSequenceController();

        $request = Request::create('/ncf-sequences', 'POST', [
            'ncf_type_id'   => 1,
            'start_number'  => 200,
            'end_number'    => 100,
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $controller->validatePayload($request);
    }

    /** @test */
    public function falla_si_valid_from_es_mayor_que_valid_until()
    {
        $controller = new NcfSequenceController();

        $request = Request::create('/ncf-sequences', 'POST', [
            'ncf_type_id'   => 1,
            'start_number'  => 1,
            'end_number'    => 10,
            'valid_from'    => '2025-12-31',
            'valid_until'   => '2025-01-01',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $controller->validatePayload($request);
    }

    /** @test */
    public function asigna_current_number_por_defecto_si_no_se_envia()
    {
        $controller = new NcfSequenceController();

        $request = Request::create('/ncf-sequences', 'POST', [
            'ncf_type_id'   => 1,
            'start_number'  => 10,
            'end_number'    => 20,
        ]);

        $payload = $controller->validatePayload($request);

        // current_number debe ser start_number - 1
        $this->assertEquals(9, $payload['current_number']);
    }
}