<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CategoryRepository;

class HomeController extends BaseController
{
    protected $categoryRepository;
    protected $articleRepository;
    public function __construct(
        CategoryRepository $categoryRepository,
        ArticleRepository $articleRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * 查询热点文章/文章的作者
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        header("Access-Control-Allow-Origin:*");
        $cates = $this->categoryRepository
                ->with(['Article' => function ($a){
                    $a->select('id','cate_id','title')
                        ->orderBy('like','desc')
                        ->limit(5);
                }])
                ->select('id','name')
                ->get()->toArray();
        $articles = $this->articleRepository
                    ->withCount(['Comment'])
                    ->with(['Userinfo' => function ($u){
                        $u->select('id','name','img_path');
                    }])
                    ->with(['Cates' =>function ($c){
                        $c->select('id','name');
                    }])
                    ->where('status','=',1)
                    ->where('publish','=',1)
                    ->orderBy('like','desc')
                    ->paginate(4)
                    ->toArray();
        $arr['cate'] = $cates;
        $arr['article'] = $articles;
        return response_success($arr);
    }
}
