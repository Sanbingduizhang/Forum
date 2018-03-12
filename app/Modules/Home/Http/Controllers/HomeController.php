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

    public function index()
    {
        $cates = $this->categoryRepository
            ->with(['Article' => function ($q){
                $q->with(['Userinfo' => function ($u){
                    $u->select('id','name');
                }])
                ->select('id','cate_id','user_id','title','desc')
                    ->where('status','=',1)
                    ->where('publish','=',1)
                    ->orderBy('like','desc');
            }])
            ->all();
        if(!$cates){
            return response_failed('not any datas');
        }
        return response_success($cates->toArray());
    }
}
