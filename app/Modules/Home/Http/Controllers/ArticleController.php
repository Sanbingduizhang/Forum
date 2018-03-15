<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\ArticleRequest;
use App\Modules\Home\Models\Article;
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
     * 分类显示对应的所有文章
     * @param $cateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($cateId)
    {
        header("Access-Control-Allow-Origin:*");
        $cateId = (int)$cateId;
        $cateIdRes = $this->categoryRepository->find($cateId);
        if (!$cateIdRes){
            return response_failed('cate is not exists');
        }
        $articleRes = $this->articleRepository
            ->withCount(['Comment'])
            ->with(['UserInfo' => function($us){
                $us->select('id','name');
            }])
            ->where(['cate_id' => $cateId])
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES)
            ->orderBy('like','desc')
            ->paginate(6)->toArray();
        foreach ($articleRes['data'] as $k => $v){
            unset(
                $articleRes['data'][$k]['status'],
                $articleRes['data'][$k]['publish'],
                $articleRes['data'][$k]['created_at'],
                $articleRes['data'][$k]['is_rec'],
                $articleRes['data'][$k]['user_id']);
        }
        $articleRes['cate_name'] = $cateIdRes->toArray()['name'];
        $articleRes = unsetye($articleRes);
        if(!$articleRes){
            return response_success([]);
        }
        return response_success($articleRes);

    }
    /**
     * 作者修改文章时候的显示页面
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ushow($id)
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

    /**
     * 查找单个文章所有信息
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uindex($id)
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

    /**
     * 作者添加文章
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
     * 作者更新文章
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
            return response_success(['message' => 'update Resources successful']);
        }
        return response_failed('update Resources failed');
    }
    /**
     * 作者或者管理员删除文章
     */
    public function del($id)
    {
        $id = (int)$id;
        //判断是否存在
        $idRes = $this->articleRepository->find($id);
        if(!$idRes){
            return response_failed('not exists');
        }
        $delRes = $idRes->delete();
        if($delRes){
            return response_success(['message' => 'del Resources successful']);
        }
        return response_failed('del Resources failed');
    }
}
