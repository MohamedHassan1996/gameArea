<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(array $data): User
    {
        $credentials = [
            'username' => $data['username'],
            'password' => $data['password'],
        ];

        // 1. Validate credentials without login
        if (!Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid username or password.'],
            ]);
        }

        // 2. Get user
        $user = User::where('username', $data['username'])
            ->firstOrFail();

        // 3. Check account status
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'username' => ['Your account is inactive.'],
            ]);
        }

        // 4. Login
        Auth::attempt($credentials);

        request()->session()->regenerate();

        return $user;
    }
}
