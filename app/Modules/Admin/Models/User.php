<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    protected $table = 'userinfo';
    protected $guarded = ['id'];
}
