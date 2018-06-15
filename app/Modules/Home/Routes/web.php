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
    //登陆注册功能
    Route::post('/login','LoginController@login');
    Route::get('/loginOut','LoginController@loginOut');
    Route::get('/loginStatus/param/{param}','LoginController@loginStatus');

    //文章综合
    Route::group(['prefix' => 'article'],function(){
        Route::get('/uindex/{id}', 'ArticleController@uindex');
        Route::get('/index/{id?}','ArticleController@index');
        Route::get('/ushow/{id}', 'ArticleController@ushow');
        Route::get('/cate', 'ArticleController@cate');
        Route::post('/create', 'ArticleController@create')->middleware('checkauth');
        Route::post('/update/{id}', 'ArticleController@update')->middleware('checkauth');
        Route::post('/del/{id}', 'ArticleController@del')->middleware('checkauth');
    });

    //图片综合
    Route::group(['prefix' => 'photo'],function(){
        Route::post('/uploads', 'PhotoController@uploads')->middleware('checkauth');
        Route::get('/index', 'PhotoController@index');
        Route::get('/show/{id}', 'PhotoController@show');
        Route::get('/showImg', 'PhotoController@showImg');
    });

    //评论综合
    Route::group(['prefix' => 'comment'],function(){

        Route::get('/imgComment', 'CommentController@imgComment');
        Route::get('/imgReply', 'CommentController@imgReply');
        //评论操作
        Route::post('/comAdd', 'CommentController@comAdd')->middleware('checkauth');
        Route::get('/comDel/id/{id}/cate/{cate}', 'CommentController@comDel')->middleware('checkauth');
        //回复操作
        Route::post('/comRepAdd', 'CommentController@comRepAdd')->middleware('checkauth');
        Route::post('/repRepAdd', 'CommentController@repRepAdd')->middleware('checkauth');
        Route::get('/repDel/id/{id}', 'CommentController@repDel')->middleware('checkauth');
    });

    //点赞相关
    Route::group(['prefix' => 'like'],function(){
        Route::post('/LikeGo', 'CommentController@LikeGo')->middleware('checkauth');
    });
});
