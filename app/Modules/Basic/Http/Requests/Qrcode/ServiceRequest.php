<?php

namespace App\Modules\Basic\Http\Requests\Qrcode;

use App\Modules\Basic\Http\Requests\ApiBaseRequest;

class ServiceRequest extends ApiBaseRequest
{
    protected $rules = [
        'base_url'=>'required',
        'school_id'=>'required',
        'school_name'=>'required',
        //'master_id'=>'required',
        'quick'=>'required',
        'record'=>'required',
        'rtmp'=>'required',
    ];
}
