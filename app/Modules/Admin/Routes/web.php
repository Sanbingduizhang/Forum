<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use \App\Http\Middleware\CheckAuth;

Route::group(['prefix' => 'admin'], function () {
    Route::post('/login','LoginController@login');
    Route::get('/index/{cateId?}', 'AdminController@index');
    Route::get('/show/{id}', 'AdminController@show');
    Route::get('/del/{id}', 'AdminController@del');
    Route::post('/update/{id}', 'AdminController@update');
    Route::post('/create', 'AdminController@create');
});
