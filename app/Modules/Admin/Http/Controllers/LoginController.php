<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Repositories\UserRepository;
use Illuminate\Http\Request;

class LoginController extends BaseController
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    //
    public function login(Request $request)
    {
        $u = $request->get('u');
        $p = $request->get('p');
        $res = $this->userRepository
            ->where(['username' => $u])
            ->where(['pwd' => $p])
            ->get();
        return 'login';
    }
    public function index()
    {

    }
}
