<?php

namespace App\Modules\Home\Http\Requests;

use App\Modules\Basic\Http\Requests\ApiBaseRequest;

class LoginRequest extends ApiBaseRequest
{
    protected $rules = [
        'username' => 'string|min:6,max:12',
        'password' => 'string|min:6,max:12',
    ];
//
    protected $messages = [
        'name' => '用户名大于6小于12',
        'password' => '密码大于6小于12',
    ];
}
