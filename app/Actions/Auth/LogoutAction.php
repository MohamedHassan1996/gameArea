<?php

namespace App\Actions\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LogoutAction
{
    public function execute(Request $request): void
    {
        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        $guard->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
