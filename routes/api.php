<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', UserController::class);
    Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
    
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/user/verify-email/{id}', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/user/resend-verify-email', [EmailVerificationController::class, 'resendEmail']);

Route::post('/forgot-password', [PasswordController::class, 'sendResetLink']);
Route::post('/set-new-password', [PasswordController::class, 'setNewPassword']);