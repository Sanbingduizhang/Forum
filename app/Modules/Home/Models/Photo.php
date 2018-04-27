<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    //
    protected $table = 'photo';
    protected $guarded = ['id'];

    /**
     * 关联相册表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function photoCate()
    {
        return $this->belongsTo(PhotoCate::class,'cate_id','id');
    }

    /**
     * 关联用户表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function User()
    {
        return $this->belongsTo(Userinfo::class,'userid','id');
    }

    /**
     * 关联评论表
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Comment()
    {
        return $this->hasMany(Comment::class,'article_id','id');
    }
}
