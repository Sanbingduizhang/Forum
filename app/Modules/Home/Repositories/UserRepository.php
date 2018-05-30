<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Userinfo;
use Illuminate\Http\Request;


class UserRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Userinfo::class;
    }

    /**
     * 用户登陆
     * @param Request $request
     * @return array
     */
    public function UserinfoRequest(Request $request)
    {
        $options = [
            'username' => htmlspecialchars($request->get('username','')),
            'pwd' => htmlspecialchars($request->get('password','')),
            'session' => htmlspecialchars($request->get('token','')),
        ];
        return $options;
    }

    /**
     * 用户注册和修改资料使用
     * @param Request $request
     * @return array
     */
    public function UserinfoUpRequest(Request $request)
    {
        $options = [
            'username' => $request->get('username'),
            'pwd' => $request->get('password'),
            'name' => $request->get('password'),
            'img_path' => $request->get('password'),
            'email' => $request->get('password'),
            'phone' => $request->get('password'),
            'birthday' => $request->get('password'),
            'age' => $request->get('password'),
            'sex' => $request->get('password'),
        ];
        return $options;
    }
}