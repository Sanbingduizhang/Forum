<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'category';
    protected $primaryKey = 'id';
    public function Article()
    {
        return $this->hasMany(Article::class,'cate_id','id');
    }
}
