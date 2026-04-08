<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        $role = Role::query()->create(['name' => 'student', 'display_name' => 'Student']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 's@example.com',
            'password' => Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $res->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'email', 'role_id', 'first_name', 'last_name', 'full_name'],
            ]);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}

