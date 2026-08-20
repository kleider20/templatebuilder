<?php

use App\Http\Controllers\Dashboard\DashboardPlantillasController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'landing/welcome/welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard/dashboard/dashboard')->name('dashboard');
    Route::get('dashboard/plantillas', [DashboardPlantillasController::class, 'index'])
        ->name('dashboard.plantillas');
});

require __DIR__.'/settings.php';

require __DIR__.'/templates.php';
