<?php

namespace App\Modules\Basic\Http\Middleware;

use Closure;
use phpCAS;

class Authorization
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
        if($this->casVerify()==false){
            if($request->ajax()) {
                return response('Unauthorized.', 401);
            }
            return redirect()->route('basic.login')->send();
        }
        return $next($request);
    }

    /**
     * @return true
     */
    protected function casVerify()
    {
        phpCAS::client(CAS_VERSION_2_0, config('cas.host'), config('cas.port'), config('cas.context'));
        if(phpCAS::checkAuthentication())
        {
            return phpCAS::getUser();
        }
        return false;
    }
}
