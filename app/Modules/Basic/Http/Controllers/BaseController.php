<?php

namespace App\Modules\Basic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class BaseController extends Controller
{
    protected $current_user;
    protected $timestamp;

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
}
