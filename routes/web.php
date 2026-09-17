<?php

use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
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

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/reservations', [AdminReservationController::class, 'index'])
        ->name('reservations.index');
    Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])
        ->name('reservations.edit');
    Route::patch('/reservations/{reservation}', [AdminReservationController::class, 'update'])
        ->name('reservations.update');
});
