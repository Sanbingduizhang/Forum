<?php

namespace App\Modules\Admin\Http\Requests;

use App\Modules\Basic\Http\Requests\ApiBaseRequest;

class UserRequest extends ApiBaseRequest
{
    protected $rules = [
        'uname' => 'required|string|min:6|max:12',
        'pwd' => 'required|string|min:6|max:12',
    ];
//
    protected $messages = [
        'uname.required' => '用户名必须',
        'pwd.required' => '密码必须',
        'uname.string' => '用户名必须时字符串',
        'pwd.string' => '密码必须时字符串',
        'uname.min' => '用户名必须大于6位',
        'pwd.min' => '密码必须必须大于6位',
        'uname.max' => '用户名必须小于12位',
        'pwd.max' => '密码必须小于12位',

    ];
}
