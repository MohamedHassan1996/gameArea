<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\LoggedInUserResource;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $loginRequest, LoginAction $loginAction)
    {

        $user = $loginAction->execute($loginRequest->validated());

        $loginRequest->session()->regenerate();

        return ApiResponse::success(new LoggedInUserResource($user));
    }
}
