<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\ArticleRequest;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CategoryRepository;


class ArticleController extends BaseController
{
    protected $articleRepository;
    protected $categoryRepository;
    public function __construct(
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * 修改文章时候的显示页面
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $id = (int)$id;
        $articleRes = $this->articleRepository
            ->with(['Cates' => function ($c){
                $c->select('id','name');
            }])
            ->select('id','title','desc','content','cate_id','status','like')
            ->where(['id' => $id])
            ->get()
            ->toArray();
        if($articleRes) {
            return response_success($articleRes);
        }
        return response_failed('not exists');
    }

    public function index($id)
    {
        $id = (int)$id;
        $findRes = $this->articleRepository->find($id);
        if(!$findRes) {
            return response_failed('not exists');
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

    /**添加文章
     * @param ArticleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ArticleRequest $request)
    {
        //获取数据
        $options = $this->articleRepository->articleAddRequest($request);
        //查找是否有此分类
        $cateRes = $this->categoryRepository->find($options['cate_id']);
        if (!$cateRes) {
            return response_failed('not exist');
        }
        //添加数据到article表中
        $res = $this->articleRepository->create($options);
        if($res){
            return response_success(['message' => 'add source successful']);
        }
        return response_failed('add source failed');
    }

    /**
     * 更新文章
     * @param ArticleRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ArticleRequest $request,$id)
    {
        $id = (int)$id;
        $options = $this->articleRepository->articleUpdateRequest($request);
        //判断是否存在
        $idRes = $this->articleRepository->find($id);
        if(!$idRes){
            return response_failed('not exists');
        }
        //填充并更新数据
        $idRes->fill($options);
        $updateRes = $idRes->save();
        if($updateRes){
            return response_success(['message' => 'update soruce successful']);
        }
        return response_failed('update soruce failed');
    }
}
