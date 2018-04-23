<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoCate extends Model
{
    protected $table = 'photoCate';
    protected $guarded = ['id'];
    public function Photo()
    {
        return $this->hasMany(Photo::class,'cate_id','id');
    }
    public function User()
    {
        return $this->belongsTo(User::class,'use_id','id');
    }
}
