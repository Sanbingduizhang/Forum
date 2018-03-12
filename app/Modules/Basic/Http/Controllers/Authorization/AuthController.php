<?php

namespace App\Modules\Basic\Http\Controllers\Authorization;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Basic\Facade\OpenPlatform;
use Illuminate\Support\Facades\Cookie;
use App\Modules\Basic\Support\CacheRepo as Cache;

use phpCAS;
use Firebase\JWT\JWT;

use Log;

class AuthController extends BaseController
{
    public function login(){
        phpCAS::client(CAS_VERSION_2_0, config('cas.host'), config('cas.port'), config('cas.context'));
        phpCAS::setNoCasServerValidation();
        phpCAS::handleLogoutRequests();
        phpCAS::forceAuthentication();

        $this->autoLogin(phpCAS::getUser());
    }

    /**
     * @param $user_code
     */
    protected function autoLogin($user_code)
    {
        // $user_info = OpenPlatform::setUuid($user_code)->user_info();
        // $cookie_value = json_encode($user_info,JSON_UNESCAPED_UNICODE);
        // header('Location:'.route('backend.dashboard'));

        //　从平台获取用户数据放并放入缓存，后续优先从缓存读取
        $userInfo = OpenPlatform::setUuid($user_code)->user_info();
        Cache::forever(Cache::KEY_JWT_USERINFO . $user_code, $userInfo);

        Cookie::queue(config('cas.cookie_name'),$user_code,config('cas.cookie_expire'));
        $token = $this->issueJwtToken($userInfo);
        $fonrt_respone = config('front.response');

        $jump_url = "{$fonrt_respone}?token={$token}&it={$userInfo["type"]}&exp=".config('jwt.exp');
        header("Location:{$jump_url}");
    }

    /**
     * @description 签发jwt token
     * @param array $userInfo 教学平台用户信息
     *
     * @Payload
     * |- iss (Issuer) Token签发者
     * |- sub (Subject) 主题
     * |- aud (Audience) 接收者
     * |- exp (Expiration Time) 过期时间-UNIX时间戳，必须大于签发时间
     * |- nbf (Not Before) 指定一个UNIX时间戳之前，此TOKEN是不可用的
     * |- iat (Issued At) 签发时间-UNIX时间戳
     * |- jti (JWT ID) Token唯一身份标识
     * |- uuid 教学平台用户标识
     * |- urole 教学平台用户身份 [admin | teacher]
     * @return string
     */
    protected function issueJwtToken($userInfo)
    {
        $payload  = [
            'iss'=>config('jwt.iss'),
            'sub'=>config('jwt.sub'),
            'aud'=>config('jwt.aud'),
            'exp'=>$this->timestamp + config('jwt.exp'),
            'iat'=>$this->timestamp,
            'uuid'=>$userInfo['usercode'],
            'urole'=>$userInfo['identity'],
        ];
        $token = JWT::encode($payload,config('jwt.key'),config('jwt.alg'));
        return $token;
    }

    /**
     * @return void
     */
    public function logout(){
        setcookie('PHPSESSID','',time()-7200,'/');
        Cookie::queue(config('cas.cookie_name'),null,-1);
        $redirect_url = $this->buildRedirectLogout();



        header('Location:'.$redirect_url);
    }


    /**
     * @return \Illuminate\Config\Repository|mixed|string
     */
    protected function buildRedirectLogout()
    {
        $redirect_url = config('cas.logout_url');
        $redirect_url.= '?service='.$this->service_url();
        $redirect_url.= '&_back='.$this->_back_url();
        return $redirect_url;
    }

    /**
     * @return string
     */
    protected function _back_url()
    {
        return urlencode(route('basic.login'));
    }

    /**
     * http://10.10.10.167:8080/cas/logout
     * @return string
     */
    protected function service_url()
    {
        $logout_url = config('cas.logout_url');
        $service = str_replace('/cas/logout','/Casclient/index.jsp',$logout_url);
        return urlencode($service);
    }
}
