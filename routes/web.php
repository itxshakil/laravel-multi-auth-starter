<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('media/{media}/temporary', [MediaController::class, 'temporary'])
    ->name('media.temporary')
    ->middleware('signed');

require __DIR__.'/settings.php';
