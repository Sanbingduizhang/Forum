<?php

namespace App\Http\Middleware;

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
        $mem = new \Memcache();
        $mem->connect('127.0.0.1',11211);
        $token = $mem->get($tokenQ);
        if(!$token){
            return response_failed('请登陆');
        }

        $tokenH = explode('+',$tokenQ);
        if((time() - $tokenH[1]) < 7200) {
            $request->attributes->add($token);
            return $next($request);
        }
        return redirect()->action('LoginController@loginStatus',$tokenQ);
    }
}
