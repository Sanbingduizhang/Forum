<?php

namespace App\Modules\Basic\Http\Middleware;

use Closure;

class TeacherAuthorization
{
    const USER_ROLE_TEACHER = 'teacher';

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
        if (!isset($jwt['urole']) || $jwt['urole'] !== self::USER_ROLE_TEACHER) {
            return response('Not Teacher', 403);
        }
        return $next($request);
    }

}
