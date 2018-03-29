<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\ArticleRequest;
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
     * @param AArticleRepository $articleRepository
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
    public function index($cateId = '')
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
    /**
     * 作者添加文章
     * @param ArticleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ArticleRequest $request)
    {
        htmlHead();
        //获取数据
        $options = $this->articleRepository->articleAddRequest($request);
        $options['user_id'] = 2;
        //查找是否有此分类
        $cateRes = $this->categoryRepository->find($options['cate_id']);
        if (!$cateRes) {
            return response_failed('not exist');
        }
        $url = "/file/" . $options['cate_id'] . $options['user_id'] . strtotime(date(now())) . uniqid() . '.doc';
        file_put_contents($url,$options['content']);
        $options['content'] = $url;
        //添加数据到article表中
        $res = $this->articleRepository->create($options);
        if($res){
            return response_success(['message' => 'add source successful']);
        }
        return response_failed('add source failed');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $id = (int)$id;
        $articleId = $this->articleRepository->find($id);
        if (!$articleId){
            return response_failed('not exits');
        }

    }

    /**
     * 管理员端显示单个要修改的文章数据
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $id = (int)$id;
        $articleId = $this->articleRepository->find($id);
        if (!$articleId){
            return response_failed('not exits');
        }
        $articleRes = $articleId->toArray();
        return response_success($articleRes);
    }

    /**
     * 管理员端删除文章
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function del($id)
    {
        $id = (int)$id;
        $articleId = $this->articleRepository->find($id);
        if (!$articleId){
            return response_failed('not exits');
        }
        $articleDel = $articleId->delete();
        if ($articleDel){
            return response_success(['message' => 'del successful']);
        }
        return response_failed('del failed');
    }
}
