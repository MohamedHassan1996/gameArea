<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use Illuminate\Support\Facades\Route;





Route::prefix('/auth')->middleware('locale')->group(function (): void {
    Route::name('auth.')->group(function (): void {
        Route::post('/login', LoginController::class);
    });
});
