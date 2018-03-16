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

Route::group(['prefix' => 'home'], function () {
    Route::get('/index', 'HomeController@index');
    Route::get('/show/{id}', 'HomeController@show');
    Route::get('/', function (){
        dd('这是home页面');
    });
    Route::group(['prefix' => 'article'],function(){
        Route::post('/create', 'ArticleController@create');
        Route::get('/uindex/{id}', 'ArticleController@uindex');
        Route::get('/index/{id?}','ArticleController@index');
        Route::get('/ushow/{id}', 'ArticleController@ushow');
        Route::post('/update/{id}', 'ArticleController@update');
        Route::post('/del/{id}', 'ArticleController@del');
    });
});
