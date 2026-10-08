<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\BusinessException;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Configuration\Exceptions;

final class BusinessExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(
            fn (BusinessException $exception) =>
                ApiResponse::error(
                    message: $exception->getMessage(),
                    errors: $exception->errors,
                    status: $exception->status,
                )
        );
    }
}
