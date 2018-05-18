<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Comment;
use Illuminate\Http\Request;


class CommentRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Comment::class;
    }
    //获取评论添加的相关数据
    public function addComRequest(Request $request)
    {
        $options = [
            'content' => $request->get('content',''),
            'article_id' => $request->get('id',''),
            'cate' => $request->get('cate',''),
        ];
        return $options;
    }


}