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

Route::get('home/reservas', 'ReservaController@index');
Route::get('home/reservas', 'ReservaController@index');
Route::resource('reserva','ReservaController');