<?php

namespace App\Modules\Basic\Http\Middleware;

use Closure;

class AdminAuthorization
{
    const USER_ROLE_ADMIN = 'admin';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $jwt = $request->get('jwt');
        if (!isset($jwt['urole']) || $jwt['urole'] !== self::USER_ROLE_ADMIN) {
            return response('Not Admin', 403);
        }
        return $next($request);
    }

}
