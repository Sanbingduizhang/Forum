<?php

namespace App\Modules\Admin\Repositories;

use App\Modules\Admin\Models\User;
use App\Modules\Basic\Repositories\BaseRepository;
use Illuminate\Http\Request;


class UserRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
     * 文章添加过滤字段
     * @param Request $request
     * @return array
     */
    public function userLoginRequest(Request $request)
    {
        $options = [
            'uname' => trim($request->get('uname',null)),
            'pwd' => md5(trim($request->get('pwd',null))),
        ];

        return $options;
    }
}