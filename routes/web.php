<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reservations/create')->name('home');
Route::get('/reservations/create', [ReservationController::class, 'create'])
    ->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])
    ->middleware('throttle:reservation-submissions')
    ->name('reservations.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Temporary placeholder for admin.reservations.index (created in Task 5)
Route::get('/admin/reservations', fn () => 'Admin Reservations Index')
    ->name('admin.reservations.index')
    ->middleware('auth');
