<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Auth\EmailVerificationController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest.admin')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);

        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');

        Route::get('two-factor-challenge', [TwoFactorController::class, 'create'])->name('two-factor.login');
        Route::post('two-factor-challenge', [TwoFactorController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->name('verification.verify')
            ->middleware('signed');
        Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
            ->name('verification.send')
            ->middleware('throttle:6,1');
    });

    // Authenticated + verified admin routes
    Route::middleware(['auth:admin', 'verified:admin.verification.notice'])->group(function (): void {
        Route::get('dashboard', fn () => Inertia::render('admin/Dashboard'))->name('dashboard');
    });
});
