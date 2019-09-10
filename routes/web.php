<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});
Route::get('/nueva-reserva', function () {
    return view('nuevaReserva');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::resource('reserva', 'ReservaController');

Route::group(['middleware' => 'auth'], function () {
    Route::get('reserva', 'ReservaController@index');
    Route::get('confirm-reserva/{reserva}', 'ReservaController@confirmReservation');
    
});
