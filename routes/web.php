<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reservations/create')->name('home');
Route::get('/reservations/create', [ReservationController::class, 'create'])
    ->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])
    ->middleware('throttle:reservation-submissions')
    ->name('reservations.store');
