<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Регистрация
Route::post('register', 'App\Http\Controllers\MainController@register');
// Аутентификация
Route::post('login', 'App\Http\Controllers\MainController@login');
// Список аэропортов
Route::get('airport', 'App\Http\Controllers\AirportsController@airport');
// Поиск рейсов
Route::get('flight', 'App\Http\Controllers\FlightsController@flights');
// Оформление бронирования
Route::post('booking', 'App\Http\Controllers\BookingController@booking');
// Инфо о бронировании
Route::get('booking/{code}', 'App\Http\Controllers\BookingController@bookingInfo');
// Инфо о всех бронированиях юзера по токену
Route::get('booking', 'App\Http\Controllers\BookingController@mybrone');
// Инфо о юзере по токену
Route::get('user', 'App\Http\Controllers\BookingController@user');