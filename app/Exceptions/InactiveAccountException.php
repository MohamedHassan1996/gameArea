<?php

namespace App\Exceptions\Auth;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessException;

final class InactiveAccountException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Your account is inactive.',
            status: HttpStatusCode::FORBIDDEN,
        );
    }
}
