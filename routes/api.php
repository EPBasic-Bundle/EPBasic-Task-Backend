<?php
use Illuminate\Http\Request;

use App\Http\Middleware\ApiAuthMiddleware;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'cors', 'prefix' => 'api'], function(){});

//Rutas controlador Usuario
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::put('user/update', 'UserController@update');

Route::get('timetable', 'TimetableController@index');
Route::post('timetable', 'TimetableController@store');

Route::get('subjects', 'SubjectController@index');
Route::post('subject', 'SubjectController@store');
Route::put('subject/update', 'SubjectController@update');
