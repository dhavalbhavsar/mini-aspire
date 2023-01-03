<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use WithFaker;

    /**
     * Register customer
     *
     * @return void
     */
    public function test_user_register() {

        $fakerPassword = $this->faker->password;

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $fakerPassword,
            'password_confirmation' => $fakerPassword,
            'role' => 'customer'
        ];

        $response = $this->post('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
    }

    /**
     * Login customer
     *
     * @return void
     */
    public function test_user_login() {

        $password = 'admin@123';
        $email = 'jon.clay@'.time().'.com';

        $payload = [
            'name' => 'Jon clay',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'customer'
        ];

        $response = $this->post('/api/v1/auth/register', $payload);

        $response->assertStatus(201);

        $payload = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->post('/api/v1/auth/login', $payload);

        $response->assertStatus(200);
    }

}
