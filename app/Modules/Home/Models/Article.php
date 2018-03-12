<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    protected $table = 'article';
    protected $primaryKey = 'id';
    public function UserInfo()
    {
        return $this->belongsTo(Userinfo::class,'user_id','id');
    }
}
