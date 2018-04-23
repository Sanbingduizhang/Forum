<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'photo';
    protected $guarded = ['id'];
    public function photoCate()
    {
        return $this->belongsTo(PhotoCate::class,'cate_id','id');
    }
    public function User()
    {
        return $this->belongsTo(User::class,'userid','id');
    }
}
