<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Admin\Http\Requests\UserRequest;
use App\Modules\Admin\Repositories\CacheRepository;
use App\Modules\Admin\Repositories\UserRepository;
use App\Modules\Basic\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class LoginController extends BaseController
{
    protected $userRepository;
    protected $cacheRepository;
    public function __construct(
        UserRepository $userRepository,
        CacheRepository $cacheRepository
)
    {
        $this->userRepository = $userRepository;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * 登陆认证
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $options = $this->userRepository->userLoginRequest($request);
        if(empty($options['uname']) || empty($options['pwd'])) {

            return response_failed('用户名或者密码不能为空');
        }
        $res = $this->userRepository
            ->where(['username' => $options['uname']])
            ->where(['pwd' => $options['pwd']])
            ->first();
        if($res){
            $res = $res->toArray();
            $tokenbefore = md5($res['username']) . uniqid() . '+' .  time();
            $time = explode('+',$tokenbefore);
            $cacheSave = $this->cacheRepository->create([
                'token' => $tokenbefore,
                'time' => $time[1],
                'uid' => $res['id'],
            ]);
            if($cacheSave){
                //存储token到session中
                session(['token' => $tokenbefore]);
                return response_success(['token' => $tokenbefore]);
            }
            return response_success(['token' => '']);
        }

        return response_failed('failed');
    }

    public function index()
    {

    }
}
