<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_access_account(): void
    {
        $this->post('/register', ['name' => 'Ava Home', 'email' => 'ava@example.test', 'password' => 'password-123', 'password_confirmation' => 'password-123'])->assertRedirect('/account');
        $this->assertAuthenticatedAs(User::where('email', 'ava@example.test')->first());
        $this->get('/account')->assertOk()->assertSee('Ava Home');
    }

    public function test_inactive_users_cannot_login_and_admin_route_is_forbidden_to_customers(): void
    {
        $user = User::create(['name' => 'Inactive', 'email' => 'inactive@example.test', 'password' => Hash::make('password-123'), 'role' => 'customer', 'is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password-123'])->assertSessionHasErrors('email');
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin')->assertForbidden();
    }
}
