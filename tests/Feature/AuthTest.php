<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    //https://www.php.net/manual/en/language.oop5.constants.php
    private const USER = [
    "first_name" => "Bob",
    "last_name" => "Marley",
    "email" => "example@test.com",
    "login" => "test",
    "password" => "test123456",
    "phone" => "418-555-1234"
    ];

    /**
     * A basic feature test example.
     */
    public function test_auth_route_overflow_is_throttle(): void
    {
        $json = [
            "login"=> "test",
            "password" => "test"
        ];

        for ($i=0; $i < 5; $i++) { 
            $response = $this->postJson('/api/signin', $json);
            $response->assertStatus(UNAUTHORIZED);
        }

        $response = $this->postJson('/api/signin', $json);
        $response->assertStatus(429);
    }

    public function test_route_signup_with_missing_field(): void 
    {
        $json = [
            "email" => "example@test.com",
            "login" => "test",
            "password" => "test123456"
        ];

        $response = $this->postJson('/api/signup', $json);
        $response->assertStatus(INVALID_DATA);
    }

    public function test_route_signup_with_bad_password(): void 
    {
        $json = [
            ...self::USER,
            "password" => "bad"
        ];

        $response = $this->postJson('/api/signup', $json);
        $response->assertStatus(INVALID_DATA);
    }

    public function test_route_signup_add_to_data_base(): void 
    {
        $response = $this->postJson('/api/signup', self::USER);
        $response->assertStatus(CREATED);
        $this->assertDatabaseHas('users', [
            "email" => self::USER["email"],
            "login" => self::USER["login"]
        ]);
    }

    public function test_route_signin_with_missing_field()
    {
        $json = [
            "login"=> "test"
        ];

        $response = $this->postJson('/api/signin', $json);
        $response->assertStatus(INVALID_DATA);
    }

    public function test_route_signin_with_bad_credentials()
    {
        $this->postJson('api/signup', self::USER);

        $response = $this->postJson('/api/signin', [
            "login" => self::USER["login"],
            "password" => "test"
        ]);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_route_signin_to_an_account()
    {
        $this->postJson('/api/signup', self::USER);

        $response = $this->postJson('/api/signin', [
            "login"=> self::USER["login"],
            "password" => self::USER["password"]
        ]);

        $user = Auth::user();
        //https://docs.phpunit.de/en/12.5/assertions.html#assertcount
        $this->assertCount(1, $user->tokens);
        //https://laravel.com/docs/master/http-tests#authentication-assertions
        $this->assertAuthenticated();
        $response->assertJsonStructure([
            'token'
        ]);
        $response->assertStatus(OK);
    }

    public function test_route_refresh_without_token()
    {
        $response = $this->postJson('api/refresh', []);

        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_route_refresh_with_token()
    {
        $user;
        Sanctum::actingAs(
            $user = User::factory()->create(), ['*']
        );

        $response = $this->postJson('api/refresh', []);
        
        //https://docs.phpunit.de/en/12.5/assertions.html#assertcount
        $this->assertCount(1, $user->tokens);
        $response->assertJsonStructure([
            'token'
        ]);
        $response->assertStatus(OK);
    }

    public function test_route_signout_without_token()
    {
        $response = $this->postJson('api/signout', []);

        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_route_signout_with_token()
    {
        $user;
        Sanctum::actingAs(
            $user = User::factory()->create(), ['*']
        );

        $response = $this->postJson('api/signout', []);
        
        $this->assertCount(0, $user->tokens);
        $this->assertDatabaseHas('users', [
            "email" => $user->email,
            "login" => $user->login
        ]);
        $response->assertStatus(NO_CONTENT);
    }

    public function test_route_me_without_token()
    {
        $response = $this->getJson('api/me');

        $response->assertStatus(UNAUTHORIZED);
    }

    public function test_route_me_with_token()
    {
        $user;
        Sanctum::actingAs(
            $user = User::factory()->create(), ['*']
        );

        $response = $this->getJson('api/me');
        
        $response->assertJsonFragment([
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "login" => $user->login,
            "phone" => $user->phone
        ]);
        $response->assertStatus(OK);
    }
}
