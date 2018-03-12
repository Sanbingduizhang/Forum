<?php

namespace App\Modules\Basic\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Basic\Facade\OpenPlatform;
use App\Modules\Basic\Support\CacheRepo as Cache;

use InvalidArgumentException;

class ApiBaseController extends Controller
{
    protected $current_user;
    protected $timestamp;
    protected $jwt;

    public function __construct()
    {
        $this->middleware(function(Request $request,$next){
            $this->jwt = $request->get('jwt');
            $this->current_user = Cache::rememberForever(
                Cache::KEY_JWT_USERINFO . $this->jwt['uuid'],
                function(){
                    return $this->current_user($this->jwt['uuid']);
                }
            );
            return $next($request);
        });
        $this->timestamp = time();
    }

    /**
     * @param integer $uuid
     * @return mixed
     */
    public function current_user($uuid)
    {
        try{
            $current_user = OpenPlatform::setUuid($uuid)->user_info();
            return $this->current_user = $current_user;
        }catch (InvalidArgumentException $e){
            return response_failed($e->getMessage());
        }
    }
}
