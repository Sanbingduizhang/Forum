<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Models\Article;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CategoryRepository;

class HomeController extends BaseController
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
     * 获取对应的分类文章（可限制数量）
     * @param $cateId 分类的id
     * @param null $limit 是否限制数量
     * @return mixed
     */
    private function cateID($cateId,$limit = null)
    {
        $cateData = $this->articleRepository->select('id','cate_id','title')
            ->where(['cate_id' => $cateId])
            ->where(['status' => Article::STATUS_ARTICLE_YES])
            ->where(['publish' => Article::PUBLISH_ARTICLE_YES])
            ->orderBy('like','desc');
        if($limit){
            $cateData = $cateData->limit($limit)->get()->toArray();
            return $cateData;
        }
        $cateData = $cateData->get()->toArray();
        return $cateData;
    }
    /**
     * 查询热点文章/文章的作者
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        htmlHead();
        //查询前六条分类
        $cates = $this->categoryRepository
                ->select('id','name')
                ->limit(6)
                ->get()->toArray();
        //遍历分类id查询对应的前五个文章
        foreach ($cates as $key => $val){
            $res = $this->cateID($val['id'],5);
            $cates[$key]['article'] = $res;
        }
        //查询文章
        $articles = $this->articleRepository
                    ->withCount(['Comment'])
                    ->with(['Userinfo' => function ($u){
                        $u->select('id','name','img_path');
                    }])
                    ->with(['Cates' =>function ($c){
                        $c->select('id','name');
                    }])
                    ->where('status','=',Article::STATUS_ARTICLE_YES)
                    ->where('publish','=',Article::PUBLISH_ARTICLE_YES)
                    ->orderBy('updated_at','desc')
                    ->orderBy('like','desc')
                    ->paginate(4)
                    ->toArray();
        $articles = unsetye($articles);
        //整合数据
        $arr['cate'] = $cates;
        $arr['article'] = $articles;
        return response_success($arr);
    }

    /**
     * 主页选择对应文章后显示页面
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        htmlHead();
        //获取文章id并判断是否存在
        $id = (int)$id;
        $findRes = $this->articleRepository->find($id);
        if(!$findRes) {
            return response_failed('not exists');
        }
        //判断发布和审核状态
        $status = $findRes->toArray()['status'];
        $publish = $findRes->toArray()['publish'];
        if( $status=== Article::STATUS_ARTICLE_NO || $publish=== Article::PUBLISH_ARTICLE_NO){
            return response_failed('message is error');
        }
        //查找对应文章.文章作者，文章评论，评论人等
        $articleRes = $this->articleRepository
            ->with(['UserInfo' => function($us){
                $us->select('id','name');
            }])
            ->with(['Cates' => function ($c){
                $c->select('id','name');
            }])
            ->with(['Comment' => function ($co){
                $co->with(['UserInfo' => function($u){
                    $u->select('id','name');
                }])
                ->select('id','article_id','user_id','content','likecount','created_at');
            }])
            ->select('id','title','desc','content','cate_id','like','user_id','updated_at')
            ->where(['id' => $id])
            ->get()
            ->toArray();
        return response_success($articleRes);
    }
}
