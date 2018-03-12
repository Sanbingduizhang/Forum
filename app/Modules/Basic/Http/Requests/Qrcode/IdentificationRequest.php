<?php

namespace App\Modules\Basic\Http\Requests\Qrcode;

use App\Modules\Basic\Http\Requests\ApiBaseRequest;


class IdentificationRequest extends ApiBaseRequest
{
    protected $rules = [
        'service_url'=>'required',
        'user_id'=>'required',
        'user_code'=>'required',
        'user_name'=>'required',
        'school_id'=>'required',
        'school_name'=>'required',
        'identity'=>'required'
    ];
}
