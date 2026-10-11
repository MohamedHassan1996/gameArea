<?php

namespace App\Exceptions\Auth;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessException;

final class InvalidCredentialsException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Invalid credentials.',
            status: HttpStatusCode::UNPROCESSABLE_ENTITY,
            errors: [
                //'username' => ['Invalid credentials.'],
            ],
        );
    }
}
