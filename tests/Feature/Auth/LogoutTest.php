<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private const LOGOUT_URL = '/api/v1/auth/logout';

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only starts the session middleware for stateful frontend requests.
        config()->set('sanctum.stateful', ['localhost']);

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
            'Accept' => 'application/json',
        ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        // Arrange: simulate an authenticated SPA session.
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);
        $this->actingAs($user, 'web');

        // Act
        $response = $this->postJson(self::LOGOUT_URL);

        // Assert the exact API contract.
        $response->assertOk()->assertExactJson([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);

        $this->assertGuest('web');
    }

    public function test_guest_cannot_logout(): void
    {
        // No actingAs(): the request is unauthenticated.
        $response = $this->postJson(self::LOGOUT_URL);

        // Requires the logout route to be protected by auth:sanctum.
        $response->assertUnauthorized();
        $this->assertGuest('web');
    }

    public function test_authenticated_user_cannot_access_protected_endpoint_after_logout(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($user, 'web');

        $this->postJson(self::LOGOUT_URL)->assertOk();
        $this->assertGuest('web');

        // Temporary route exercises the real auth:sanctum middleware,
        // without assuming the profile endpoint already exists.
        Route::middleware('auth:sanctum')->get('/__test/protected', function () {
            return response()->json(['authenticated' => true]);
        });

        $response = $this->getJson('/__test/protected');
        $response->assertUnauthorized();
    }
}
