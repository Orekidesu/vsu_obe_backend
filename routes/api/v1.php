<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Version 1 API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1/auth.php';

    Route::middleware(['auth:sanctum'])->group(function () {
        require __DIR__ . '/v1/role.php';
        require __DIR__ . '/v1/user.php';
    });
});
