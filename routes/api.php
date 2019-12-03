<?php
use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'cors', 'prefix' => 'api'], function () {});

//Rutas controlador Usuario
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::put('user/update', 'UserController@update');

Route::get('user/pinCode/{user_id}/{pinCode}', 'UserController@unblockUser');
Route::get('user/pinCode', 'UserController@getPinCode');
Route::get('user/change-pinCode', 'UserController@changePinCode');
Route::get('change-year/{year_id}', 'UserController@changeYear');

Route::get('timetable', 'TimetableController@index');
Route::post('timetable', 'TimetableController@store');
Route::delete('timetable', 'TimetableController@destroy');

Route::get('subjects/allToDo', 'SubjectController@indexWithAllToDo');
Route::get('subject/set-current-unity/{id}', 'SubjectController@setCurrentUnity');
Route::get('subjects', 'SubjectController@index');
Route::get('subject/all/{id}', 'SubjectController@detailWithAll');
Route::get('subject/{id}', 'SubjectController@detail');
Route::post('subject', 'SubjectController@store');
Route::put('subject/{id}', 'SubjectController@update');
Route::delete('subject/{id}', 'SubjectController@destroy');

Route::get('books/{subject_id}', 'BookController@index');
Route::get('book/getPDF/{filename}', 'BookController@getBookPDF');
Route::get('book/{id}', 'BookController@detail');
Route::post('book', 'BookController@store');
Route::put('book/{id}', 'BookController@update');
Route::delete('book/{id}', 'BookController@destroy');
Route::post('pdf/upload/{book_id}', 'BookController@pdfUpload');

Route::get('pdf/last-seen-page/{book_id}/{page_number}', 'BookController@lastSeenPage');
Route::get('pdf/saved-pages/{unity_id}', 'BookController@savedPages');
Route::post('pdf/savedPage', 'BookController@storePage');
Route::put('pdf/savedPage/{id}', 'BookController@updatePage');
Route::delete('pdf/savedPage/{id}', 'BookController@destroyPage');

Route::get('units/{subject_id}', 'UnityController@index');
Route::get('unity/{id}', 'UnityController@detail');
Route::post('unity', 'UnityController@store');
Route::put('unity/{id}', 'UnityController@update');
Route::delete('unity/{id}', 'UnityController@destroy');

Route::get('tasks/todo/{subject_id}', 'TaskController@indexToDo');
Route::get('tasks/{unity_id}', 'TaskController@index');
Route::put('task/start/{task_id}', 'TaskController@updateDeliveryDay');
Route::get('task/{id}', 'TaskController@detail');
Route::post('task', 'TaskController@store');
Route::put('task/{id}', 'TaskController@update');
Route::delete('task/{id}', 'TaskController@destroy');

Route::get('exams/todo/{subject_id}', 'ExamController@indexToDo');
Route::get('exams/{unity_id}', 'ExamController@index');
Route::get('exam/{id}', 'ExamController@detail');
Route::put('exam/start/{exam_id}', 'ExamController@updateExamDay');
Route::post('exam', 'ExamController@store');
Route::put('exam/{id}', 'ExamController@update');
Route::delete('exam/{id}', 'ExamController@destroy');

Route::get('exercise/done/{exercise_id}', 'ExerciseController@changeStatus');
Route::get('task/done/{task_id}', 'TaskController@changeStatus');
Route::get('exam/done/{exam_id}', 'ExamController@changeStatus');

Route::get('events/not-passed', 'EventController@indexNotPassed');
Route::get('events', 'EventController@index');
Route::get('event/task/{id}', 'EventController@taskEvent');
Route::get('event/exam/{id}', 'EventController@examEvent');
Route::post('event', 'EventController@store');
Route::put('event/{id}', 'EventController@update');
Route::delete('event/{id}', 'EventController@destroy');

Route::get('studies', 'StudyController@index');
Route::post('study', 'StudyController@store');
Route::put('study/{id}', 'StudyController@update');
Route::delete('study/{id}', 'StudyController@destroy');

Route::post('year', 'YearController@store');
Route::put('year/{id}', 'YearController@update');
Route::delete('year/{id}', 'YearController@destroy');

Route::get('evaluations', 'EvaluationController@index');
Route::post('evaluation', 'EvaluationController@store');
Route::put('evaluation/{id}', 'EvaluationController@update');
Route::delete('evaluation/{id}', 'EvaluationController@destroy');

Route::get('generate-pdf', 'PDFGeneratorController@index');
