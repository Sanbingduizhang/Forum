<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Comment;
use App\Modules\Home\Models\Reply;
use Illuminate\Http\Request;


class ReplyRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Reply::class;
    }
    //获取评论回复添加的相关数据
    public function addComRepRequest(Request $request)
    {
        $options = [
            'content' => $request->get('content',''),
            'comment_id' => $request->get('commentid',''),
            'pid' => $request->get('pid',0),
        ];
        return $options;
    }
    //获取回复的回复添加的相关数据
    public function addRepRepRequest(Request $request)
    {
        $options = [
            'content' => $request->get('content',''),
            'comment_id' => $request->get('commentid',''),
            'pid' => $request->get('pid',0),
        ];
        return $options;
    }


}