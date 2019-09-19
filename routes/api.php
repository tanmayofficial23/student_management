<?php

use Illuminate\Http\Request;

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

Route::post('/login', 'AuthController@login');

Route::group(['middleware' => 'auth:api'], function(){
    
    Route::get('/show', 'StudentController@showAllRecords');
    
    Route::post('/new', 'StudentController@insertNewRecord')->name('new.insertRecord');

    Route::post('/edit', 'StudentController@editRecord')->name('edit.editRecord');

    Route::post('/delete', 'StudentController@deleteRecord')->name('delete.deleteRecord');

});

?>