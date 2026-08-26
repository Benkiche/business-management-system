<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppliers_index_route_renders_successfully(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/suppliers')
            ->assertOk();
    }
}
