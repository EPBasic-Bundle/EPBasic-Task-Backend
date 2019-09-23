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
Route::get('subject/{id}', 'SubjectController@detail');
Route::post('subject', 'SubjectController@store');
Route::put('subject/{id}', 'SubjectController@update');
Route::delete('subject/{id}', 'SubjectController@destroy');

Route::get('books/{subject_id}', 'BookController@index');
Route::get('books/{id}', 'BookController@detail');
Route::post('book', 'BookController@store');
Route::put('book/{id}', 'BookController@update');
Route::delete('book/{id}', 'BookController@destroy');
