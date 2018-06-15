<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    const PUBLISH_ARTICLE_YES = 1;
    const PUBLISH_ARTICLE_NO = 0;
    const STATUS_ARTICLE_YES = 1;
    const STATUS_ARTICLE_NO = 0;
    const ISREC_ARTICLE_YES = 1;
    protected $table = 'article';
    protected $guarded = ['id'];
    public function UserInfo()
    {
        return $this->belongsTo(Userinfo::class,'user_id','id');
    }
    public function Comment()
    {
        return $this->hasMany(Comment::class,'article_id','id');
    }
    public function Cates()
    {
        return $this->belongsTo(Category::class,'cate_id','id');
    }
    public function LikeCount()
    {
        return $this->hasOne(LikeCount::class,'article_id','id');
    }
}
