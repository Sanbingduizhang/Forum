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
    Route::get('/index/{cateId?}', 'AdminController@index')->middleware(CheckAuth::class);
    Route::get('/show/{id}', 'AdminController@show')->middleware(CheckAuth::class);
    Route::get('/del/{id}', 'AdminController@del')->middleware(CheckAuth::class);
    Route::post('/update/{id}', 'AdminController@update')->middleware(CheckAuth::class);
    Route::post('/create', 'AdminController@create')->middleware(CheckAuth::class);
});
