<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

// Los "name" se conservan (profile.*, security.*) para no romper Wayfinder,
// los flujos 2FA/passkeys y los redirects internos. El grupo 'settings'
// aporta la estructura modular de prefijo.
Route::prefix('settings')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::redirect('/', '/settings/profile');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::class)
            ->name('security.edit');

        Route::put('password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');

        Route::inertia('appearance', 'settings/appearance')->name('appearance.edit');
    });
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
