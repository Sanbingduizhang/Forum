<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Admin\Repositories\CacheRepository;
use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\LoginRequest;
use App\Modules\Home\Repositories\UserRepository;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class LoginController extends BaseController
{
    protected $userRepository;
    protected $cacheRepository;

    protected $uid;
    protected $uname;


    public function __construct(
        UserRepository $userRepository,
        CacheRepository $cacheRepository)
    {
        $this->userRepository = $userRepository;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * 用户简单登陆
     * @param LoginRequest $loginRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $loginRequest)
    {
        $options = $this->userRepository->UserinfoRequest($loginRequest);
        if('' == $options['username'] || '' == $options['pwd']){
            return response_failed('请登陆');
        }
        //验证是否存在此用户
        $findRes = $this->userRepository
            ->where(['username' => $options['username'],'pwd' => $options['pwd']])
            ->first();
        if(!$findRes){
            return response_failed('用户不存在，请先注册');
        }
        //生成Token
        $tokenbefore = md5($options['username']) . uniqid() . '+' .  time();
        $time = explode('+',$tokenbefore);
        $cacheSave = $this->cacheRepository->create([
            'token' => $tokenbefore,
            'time' => $time[1],
            'uid' => $findRes->id,
        ]);
        if($cacheSave){
            //存储token
            $this->setMem($findRes->id,$tokenbefore,0,7200);
            session_start();
            $_SESSION['uid']=$findRes->id;
            $this->uid = $findRes->id;
            $this->uname = $findRes->name;
            return response_success(['token' => $tokenbefore]);
        }
        return response_success(['message' => '登陆成功']);

    }

    public function loginOut()
    {

    }
    /**
     *
     */
    public function index()
    {
        session_start();
        $res = $this->getMem(2);
        var_dump($_SESSION['uid']);
        dd($res);
    }
}
