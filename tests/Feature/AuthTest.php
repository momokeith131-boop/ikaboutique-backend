<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    public function test_un_utilisateur_peut_s_inscrire()
    {
        $email = 'test' . time() . '@example.com';
        $phone = '+223' . rand(10000000, 99999999);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => $email,
            'phone' => $phone,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'email', 'phone'],
                     'token'
                 ]);
    }

    public function test_un_utilisateur_peut_se_connecter()
    {
        $email = 'login' . time() . '@test.com';
        $phone = '+223' . rand(10000000, 99999999);

        $user = User::factory()->create([
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'user',
                     'token'
                 ]);
    }

    public function test_un_utilisateur_ne_peut_pas_se_connecter_avec_mauvais_identifiants()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid credentials',
                 ]);
    }
}
