<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FortifyLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_cannot_login_through_public_fortify_login(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $admin->assignRole($adminRole);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_users_can_login_through_public_fortify_login(): void
    {
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);
        $manager->assignRole($managerRole);

        $response = $this->post('/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($manager);
    }
}
