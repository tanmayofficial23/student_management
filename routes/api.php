<?php

use Illuminate\Http\Request;
use App\Mail\ResetPassword;

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

Route::post('/resetmail', 'AuthController@generateResetLink');

Route::post('/setnewpass', 'AuthController@resetPassword');

Route::group(['middleware' => 'auth:api'], function(){
    
    Route::get('/show', 'StudentController@showAllRecords');
    
    Route::post('/new', 'StudentController@insertNewRecord')->name('new.insertRecord');

    Route::get('/edit/{id}', 'StudentController@getEditId');

    Route::post('/edit', 'StudentController@editRecord')->name('edit.editRecord');

    Route::get('/delete/{id}', 'StudentController@deleteRecord')->name('delete.deleteRecord');

    Route::get('/logout', 'StudentController@logout');

});

?>