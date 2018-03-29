<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Http\Requests\ArticleRequest;
use App\Modules\Home\Models\Article;
use Illuminate\Http\Request;


class AArticleRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Article::class;
    }

    /**
     * 文章添加过滤字段
     * @param Request $request
     * @return array
     */
    public function articleAddRequest(Request $request)
    {
        $options = [
            'title' => $request->get('title'),
            'desc' => $request->get('desc'),
            'content' => $request->get('content'),
            'cate_id' => (int)$request->get('cateId'),
            'publish' => (int)$request->get('publish',1),
            'wordsnum' => (int)$request->get('words'),
        ];
        return $options;
    }

    /**
     * 文章修改过滤字段
     * @param Request $request
     * @return array
     */
    public function articleUpdateRequest(Request $request)
    {
        $options = [
            'user_id' => (int)$request->get('userId'),
            'title' => $request->get('title'),
            'desc' => $request->get('desc'),
            'content' => $request->get('content'),
            'cate_id' => (int)$request->get('cateId'),
            'publish' => (int)$request->get('publish'),
        ];
        return $options;
    }
}