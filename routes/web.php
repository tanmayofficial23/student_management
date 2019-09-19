<?php

Auth::routes();

Route::get('/', 'StudentController@showAllRecords');

Route::get('/new', function(){
    return view('/insertPage');
});

Route::post('/new', 'StudentController@insertNewRecord')->name('new.insertRecord');

Route::get('/{id}/edit', 'StudentController@getEditId');

Route::post('/edit', 'StudentController@editRecord')->name('edit.editRecord');

Route::get('/{id}/delete', 'StudentController@confirmDelete');

Route::post('/delete', 'StudentController@deleteRecord')->name('delete.deleteRecord');

?>