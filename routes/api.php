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

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('attendance/today', 'API\AttendanceController@index');
    Route::get('attendance/weekly', 'API\AttendanceController@weeklyAttendance');
    Route::patch('attendance/check-in/{attendance}', 'API\AttendanceController@checkIn');
    Route::patch('attendance/check-out/{attendance}', 'API\AttendanceController@checkOut');
});
