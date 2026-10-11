<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN_URL = '/api/v1/auth/login';
    private const PASSWORD = 'password123';

    /**
     * Sanctum only starts a session for requests from a configured SPA origin.
     * This exercises the stateful API path instead of a stateless API request.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sanctum.stateful', ['localhost']);

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
            'Accept' => 'application/json',
        ]);
    }

    public function test_active_user_can_login_and_receive_expected_response(): void
    {
        $user = User::factory()->create([
            'name' => 'Mohamed',
            'username' => 'mohamed',
            'password' => self::PASSWORD,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->postJson(self::LOGIN_URL, [
            'username' => $user->username,
            'password' => self::PASSWORD,
        ]);

        $response->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => '',
                'data' => [
                    'name' => $user->name,
                    // Matches the LoggedInUserResource as currently supplied.
                    // Your current User model has no email field, so this is null.
                    'email' => $user->email,
                ],
            ]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_unknown_username_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'mohamed',
            'password' => self::PASSWORD,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->postJson(self::LOGIN_URL, [
            'username' => 'not_found',
            'password' => self::PASSWORD,
        ]);

        $this->assertInvalidCredentialsResponse($response);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'password' => self::PASSWORD,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->postJson(self::LOGIN_URL, [
            'username' => $user->username,
            'password' => 'wrong_password',
        ]);

        $this->assertInvalidCredentialsResponse($response);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => self::PASSWORD,
            'status' => UserStatus::INACTIVE,
        ]);

        $response = $this->postJson(self::LOGIN_URL, [
            'username' => $user->username,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(403)
            ->assertExactJson([
                'success' => false,
                'message' => 'Your account is inactive.',
                'errors' => [],
            ]);

        $this->assertGuest('web');
    }

    public function test_inactive_user_with_wrong_password_gets_invalid_credentials_instead_of_inactive_message(): void
    {
        $user = User::factory()->create([
            'password' => self::PASSWORD,
            'status' => UserStatus::INACTIVE,
        ]);

        $response = $this->postJson(self::LOGIN_URL, [
            'username' => $user->username,
            'password' => 'wrong_password',
        ]);

        $this->assertInvalidCredentialsResponse($response);
        $response->assertJsonMissing(['message' => 'Your account is inactive.']);
    }

    public function test_username_is_required(): void
    {
        $this->assertValidationFailure(
            ['password' => self::PASSWORD],
            ['username']
        );
    }

    public function test_password_is_required(): void
    {
        $this->assertValidationFailure(
            ['username' => 'mohamed'],
            ['password']
        );
    }

    public function test_both_credentials_are_required(): void
    {
        $this->assertValidationFailure([], ['username', 'password']);
    }

    public function test_username_must_be_a_string(): void
    {
        $this->assertValidationFailure(
            ['username' => ['not-a-string'], 'password' => self::PASSWORD],
            ['username']
        );
    }

    public function test_password_must_be_a_string(): void
    {
        $this->assertValidationFailure(
            ['username' => 'mohamed', 'password' => ['not-a-string']],
            ['password']
        );
    }

    public function test_blank_strings_are_rejected(): void
    {
        $this->assertValidationFailure(
            ['username' => ' ', 'password' => ' '],
            ['username', 'password']
        );
    }

    private function assertValidationFailure(array $payload, array $fields): void
    {
        $response = $this->postJson(self::LOGIN_URL, $payload);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonValidationErrors($fields, 'errors');

        $this->assertGuest('web');
    }

    private function assertInvalidCredentialsResponse($response): void
    {
        // Your current BusinessExceptionHandler maps invalid credentials to HTTP 422.
        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('message', 'Invalid credentials.')
            ->assertJsonPath('errors', []);

        $this->assertGuest('web');
    }
}
