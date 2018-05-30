<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Repositories\CacheRepository;
use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Repositories\UserRepository;
use Closure;

class CheckAuth
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        htmlHead();
        $tokenQ = $request->get('token',1);
        session_start();
        $token = isset($_SESSION[$tokenQ]) ? $_SESSION[$tokenQ] : '';
//        $mem = new \Memcache();
//        $mem->connect('127.0.0.1',11211);
//        $token = $mem->get($tokenQ);
        if(!$token){
            return response_failed('请登陆');
        }

        $tokenH = explode('+',$tokenQ);
        if((time() - $tokenH[1]) < 7200) {
            return $next($request);
        }
        return redirect()->action('LoginController@loginStatus',$tokenQ);
    }
}
