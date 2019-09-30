<?php

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


Route::post('/register', 'AuthController@register')->name('register');

Route::post('/verify', 'AuthController@emailVerification');

Route::post('/login', 'AuthController@login')->name('login');

Route::post('/resetmail', 'AuthController@generateResetPasswordLink');

Route::post('/setnewpass', 'AuthController@resetPassword');

Route::group(['middleware' => 'auth:api'], function(){
    
    Route::get('/students/index', 'StudentController@showAllRecords');
    
    Route::post('/students/create', 'StudentController@insertNewRecord')->name('students.create');

    Route::get('/students/{id}', 'StudentController@get');

    Route::post('/students/edit', 'StudentController@editRecord')->name('students.edit');

    Route::get('/students/delete/{id}', 'StudentController@deleteRecord')->name('students.delete');

    Route::get('/logout', 'StudentController@logout');

});

?>