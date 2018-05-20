<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $table = 'comment';
    protected $guarded = ['id'];

    /**
     * 关联用户表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function UserInfo()
    {
        return $this->belongsTo(Userinfo::class,'user_id','id');
    }

    /**
     * 关联回复表
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Reply()
    {
        return $this->hasMany(Reply::class,'comment_id','id');
    }

    public function LikeCount()
    {
        return $this->has(LikeCount::class,'comment_id','id');
    }

}
