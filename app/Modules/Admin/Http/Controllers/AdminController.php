<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Models\Article;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class AdminController extends BaseController
{
    protected $categoryRepository;
    protected $articleRepository;

    /**
     * HomeController constructor.
     * @param CategoryRepository $categoryRepository
     * @param ArticleRepository $articleRepository
     */
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
    public function index(Request $request,$cateId = '')
    {
        htmlHead();
        //查询有没有cateId传进来
        //如果没有，则认为是跳转到这个页面
        //如果存在cateId则认为是在当前页面点击不同的分类
        if($cateId){
            $cateId = (int)$cateId;
            $cateIdRes = $this->categoryRepository->find($cateId);
            if (!$cateIdRes){
                return response_failed('cate is not exists');
            }
        }

        //公用查询
        $this->articleRepository = $this->articleRepository
                ->withCount(['Comment'])
                ->with(['Userinfo' => function ($u){
                    $u->select('id','name','img_path');
                }])
                ->with(['Cates' =>function ($c){
                    $c->select('id','name');
                }]);
        //查询文章
        //判断有没有cateId，如果没有，则认为查询所有
        if($cateId){
            $articles = $this->articleRepository
                ->where(['cate_id' => $cateId])
                ->orderBy('updated_at','desc')
                ->paginate(4)
                ->toArray();
        }else{
            //查询所有分类
            $cates = $this->categoryRepository
                ->select('id','name')
                ->get()->toArray();
            $arr['cate'] = $cates;
            $articles = $this->articleRepository
                ->orderBy('updated_at','desc')
                ->paginate(4)
                ->toArray();
        }

        $articles = unsetye($articles);
        //整合数据

        $arr['article'] = $articles;
        return response_success($arr);
    }
}
