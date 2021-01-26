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
Route::get('models/check-version/{version}', 'API\ModelsController@checkModelVersion');
Route::get('models/train/download', 'API\ModelsController@downloadTrain');
Route::get('models/label/download', 'API\ModelsController@downloadLabel');
Route::get('models/train-model/download', 'API\ModelsController@downloadTrainModel');
Route::get('models/version/download', 'API\ModelsController@downloadVersion');
Route::get('attendance-instant/today/{label}', 'API\AttendanceController@getAttendanceByLabel');
Route::patch('attendance/check-in/{attendance}', 'API\AttendanceController@checkIn');
Route::patch('attendance/check-out/{attendance}', 'API\AttendanceController@checkOut');

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('attendance/today', 'API\AttendanceController@index');
    Route::get('attendance/monthly', 'API\AttendanceController@monthlyAttendance');
    Route::post('models/upload', 'API\ModelsController@store');
});
