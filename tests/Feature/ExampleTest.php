<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Route::get('/test-ping', fn () => response()->json(['ok' => true]));

        $response = $this->get('/test-ping');

        $response->assertStatus(200);
    }
}
