<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\LikeCount;
use Illuminate\Http\Request;


class LikeCountRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return LikeCount::class;
    }
    //获取评论回复添加的相关数据
    public function LikeCountRequest(Request $request)
    {
        $options = [
            'id' => $request->get('id',0),              //点赞表的id
            'article_id' => $request->get('arPh_id',0),      //文章或者图片的id
            'comment_id' => $request->get('commentid',0),       //评论的id
            'reply_id' => $request->get('replyid',0),           //回复的id
            'cate' => $request->get('cate',1),                  //分类1-文章，2-图片
            'like' => $request->get('like',1),                  //0-取消赞，1-点赞
        ];
        return $options;
    }
}