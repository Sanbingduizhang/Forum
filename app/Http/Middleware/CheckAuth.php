<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Repositories\CacheRepository;
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
        $tokenQ = $request->get('token',null);
        $tokenH = session('token');
        if($tokenQ != $tokenH) {
            return response_failed('false');
        }
        $tokenH = explode('+',$tokenH);
        if((time() - $tokenH[1]) > 18000) {
            
            return response_failed(['重新登陆']);
        }

//        $token = $request->get('token',null);
//        if(empty($token)){
//            return response_failed('failed');
//        }
//        $tokenRes = $this->cacheRepository->where([
//            'token' => $token,
//            'status' => 1,
//        ])->first();
//
//        if(!$tokenRes){
//            return response_failed('sub is failed');
//        }
//        $tokenResr = $tokenRes->toArray();
//        if((time() - $tokenResr['time']) > 18000) {
//            $tokenRes->status = -1;
//            $tokenRes->save();
//            return response_failed(['重新登陆']);
//        }

        return $next($request);
    }
}
