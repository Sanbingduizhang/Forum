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
    //相册部分
    Route::group(['prefix' => 'photo'],function(){
        //上传
       Route::post('/uploadImg/{id}','PhotoController@uploadImg');
       //显示
       Route::get('/index','PhotoController@index');
       Route::get('/show/{id}','PhotoController@show');
       //相册操作
       Route::get('/pindex','PhotoController@iPhoto');
       Route::post('/pCreate','PhotoController@createPhoto');
       Route::post('/pUpdate/{id}','PhotoController@updatePhoto');
       //删除相册
       Route::post('/pdel','PhotoController@delPhoto');              //暂不使用
        //删除图片
       Route::post('/delPDetail','PhotoController@delPDetail');
    });
});
