<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $table = 'comment';
    protected $guarded = ['id'];
    public function UserInfo()
    {
        return $this->belongsTo(Userinfo::class,'user_id','id');
    }

}
