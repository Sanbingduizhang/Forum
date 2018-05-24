<?php

namespace App\Modules\Basic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class BaseController extends Controller
{
    protected $current_user;
    protected $timestamp;
    protected $uid;
    protected $uname;

    public function __construct()
    {
//        $this->current_user();
        $this->timestamp = time();
    }

    /**
     * @return mixed
     */
    public function current_user()
    {
        $currentUser4Cookie = Cookie::get(config('cas.cookie_name'));
        if($currentUser4Cookie)
        {
            $currentUser4Cookie = decrypt($currentUser4Cookie);
            $this->current_user = json_decode($currentUser4Cookie,TRUE);
        }
    }

    /**
     * @param string $message
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxResponseF($message='',$status='successful')
    {
        return response()->json([
            'status'  => $status ? : 'failed',
            'message' => $message ? : trans('backend::alert.update_successful'),
        ]);
    }
    public function userStatus()
    {

    }

    /**
     * 设置
     * @param $key
     * @param $value
     * @param string $flag
     * @param $expiration
     * @return bool
     */
    public function setMem($key,$value,$flag = 'MEMCACHE_COMPRESSED',$expiration)
    {
        $mem = new \Memcache();
        $mem->connect('127.0.0.1',11211);
        $memRes = $mem->set($key,$value,$flag,$expiration);
        return $memRes;
    }

    /**
     * 获取
     * @param $keys array|string
     * @return array|string
     */
    public function getMem($keys)
    {
        $mem = new \Memcache();
        $mem->connect('127.0.0.1',11211);
        $memRes = $mem->get($keys);
        return $memRes;
    }

    /**
     * 删除
     * @param $keys
     * @return bool
     */
    public function delMem($keys)
    {
        $mem = new \Memcache();
        $mem->connect('127.0.0.1',11211);
        if(is_array($keys)){
            foreach ($keys as $k => $v) {
                $mem->delete($v);
            }
        } else {
            $mem->delete($keys);
        }

        return true;
    }
}
