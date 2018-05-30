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
        htmlHead();
        $options = $this->userRepository->UserinfoRequest($loginRequest);

        if('' == $options['username'] || '' == $options['pwd']){
            return response_failed('请登陆');
        }
        if(strlen($options['username']) < 3 || strlen($options['username']) > 16){
            return response_failed("用户名需大于三位且小于16位");
        }
        if(strlen($options['pwd']) < 3 || strlen($options['pwd']) > 16){
            return response_failed("密码需大于三位且小于16位");
        }
        //验证是否存在此用户
        $findRes = $this->userRepository
            ->where(['username' => $options['username'],'pwd' => md5($options['pwd'])])
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
            $this->setMem($tokenbefore,['uid' => $findRes->id,'name' => $findRes->name],0,7200);
            $this->uid = $findRes->id;
            $this->uname = $findRes->name;
            return response_success(['token' => $tokenbefore]);
        }
        return response_success(['message' => '登陆成功']);

    }

    /**
     * 用户退出操作
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginOut(Request $request)
    {
        htmlHead();
        //传递token，进行判断
        $token = $request->get('token');
        //暂不使用缓存
        $tokenRes = $this->getMem($token);
        //如果缓存获取不到token，则代表已经退出登陆
        $findRes = $this->cacheRepository
            ->where(['token' => $token,'status' => 1])
            ->first();
        if (!$tokenRes && !$findRes){
            return response_success(['message' => '请您登录后重试']);
        }
        if(!$findRes)
        {
            return response_success(['message' => '您未曾登陆']);
        }
        //暂不使用清除缓存，
        $this->delMem($token);
        //修改状态
        $findRes->status = -1;
        $upRes = $findRes->save();
        if($upRes){
            return response_success(['message' => '退出成功']);
        }
        return response_failed('退出失败');
    }
    /**
     * 状态更改
     * @param $token
     * @param $tokenRes
     * @return int
     */
    public function loginStatus($param)
    {
        $mem = new \Memcache();
        $mem->connect('127.0.0.1',11211);
        $memRes = $mem->get($param);
        session_start();
        $tokenRes =isset($_SESSION[$param]) ? $_SESSION[$param] : '';
        $findRes = $this->cacheRepository
            ->where(['token' => $param,'status' => 1])
            ->first();
        if (!$tokenRes && !$findRes){
            return response_success(['message' => '请您登录后重试']);;  //请您登录后重试;
        }
        if(!$findRes)
        {
            return response_success(['message' => '您未曾登陆']);      //您未曾登陆']);
        }
        //清除缓存，
        $this->delMem($param);
        //修改状态
        $findRes->status = -1;
        $upRes = $findRes->save();
        if($upRes){
            //清除session
            return response_success(['message' => '请重新登陆']);   //退出成功
        } else {
            return response_failed('退出失败');//退出失败
        }
    }
}
