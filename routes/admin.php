<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Rute untuk panel admin (Fase 6).
| Semua rute memerlukan otorisasi melalui policy EventPolicy/RegistrationPolicy.
*/

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin area - butuh otorisasi admin
Route::middleware(['auth', 'verified', 'can:admin.dashboard'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        // Events
        Route::resource('events', EventController::class)
            ->except(['show'])
            ->shallow(true);

        // Categories
        Route::resource('categories', CategoryController::class)
            ->except(['show'])
            ->shallow(true);
    });
