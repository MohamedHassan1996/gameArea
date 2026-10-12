<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Enums\HttpStatusCode;
use App\Exceptions\Handlers\BusinessExceptionHandler;
use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

         $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        BusinessExceptionHandler::register($exceptions);


        $exceptions->render(function (ValidationException $e, Request $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            return ApiResponse::error("", $e->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        });

    })->create();
