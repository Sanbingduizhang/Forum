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

Route::group(['prefix' => 'basic'], function () {
    Route::match(['GET','POST'],'login',['uses'=>'Authorization\AuthController@login','as'=>'login']);
    Route::get('logout',['uses'=>'Authorization\AuthController@logout','as'=>'logout']);

    /**
     * 产生二维码
     */
    Route::post('qrcode/create/identification',['uses'=>'QrcodeController@identification','as'=>'qrcode.create']);
    Route::post('qrcode/create/service',['uses'=>'QrcodeController@service','as'=>'qrcode.create']);
});
