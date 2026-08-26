<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_renders_for_admin_user(): void
    {
        $role = Role::create([
            'name' => 'admin',
            'description' => 'Administrator',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/users')
            ->assertOk();
    }
}
