<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class LoginController extends BaseController
{
    protected function __construct()
    {
    }

    public function login(LoginRequest $loginRequest)
    {

    }
}
