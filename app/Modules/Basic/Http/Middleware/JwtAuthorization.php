<?php

namespace App\Modules\Basic\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use App\Exceptions\Handler;

class JwtAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next){
        $token = $request->header('Authorization');
        $jwt_token = trim(str_replace('Bearer','',$token));
        try {
            $jwt_value = (array)JWT::decode($jwt_token, config('jwt.key'), [config('jwt.alg')]);
            $request->attributes->add(['jwt'=>$jwt_value]);
        }catch (\InvalidArgumentException $e){
            return response_failed($e->getMessage());
        }catch (\UnexpectedValueException $e) {
            return response_failed('The Authorization field in the head does not exist');
        }catch (ExpiredException $e) {
            return response_failed($e->getMessage());
        }catch (SignatureInvalidException $e) {
            return response_failed($e->getMessage());
        }catch (BeforeValidException $e){
            return response_failed($e->getMessage());
        }catch (DomainException $e) {
            return response_failed($e->getMessage());
        }

        return $next($request);
    }
}
