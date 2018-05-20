<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class LikeCount extends Model
{
    protected $table = 'likecount';
    protected $guarded = ['id'];

    /**
     * 关联用户表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function UserInfo()
    {
        return $this->belongsTo(Userinfo::class,'user_id','id');
    }
}
