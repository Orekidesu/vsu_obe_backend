<?php

use App\Http\Controllers\Api\V1\Auth\ChangeUserInfoController;
use App\Http\Controllers\Api\V1\Auth\ChangeUserPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationNotificationController;

Route::post('/register', [RegisteredUserController::class, 'store'])
     ->middleware('guest')
     ->name('register');

Route::post('/login', [LoginController::class, 'store'])
     ->middleware('guest')
     ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
     ->middleware('guest')
     ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
     ->middleware('guest')
     ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
     ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
     ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
     ->middleware(['auth:sanctum', 'throttle:6,1'])
     ->name('verification.send');

Route::post('/logout', [LoginController::class, 'destroy'])
     ->middleware('auth:sanctum')
     ->name('logout');

Route::post('/change-password', [ChangeUserPasswordController::class, 'store']);
Route::post('/change-user-info', [ChangeUserInfoController::class, 'store']);