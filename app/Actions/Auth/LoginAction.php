<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\InactiveAccountException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard;

final class LoginAction
{
    public function execute(array $credentials): User
    {
        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        $inactive = false;

        $authenticated = $guard->attemptWhen(
            $credentials,
            function (User $user) use (&$inactive): bool {
                if (! $user->isActive()) {
                    $inactive = true;

                    return false;
                }

                return true;
            }
        );

        if (! $authenticated) {
            if ($inactive) {
                throw new InactiveAccountException();
            }

            throw new InvalidCredentialsException();
        }

        return $guard->user();
    }
}
