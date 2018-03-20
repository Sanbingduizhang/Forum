<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Http\Requests\ArticleRequest;
use App\Modules\Home\Models\Article;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CategoryRepository;
use Illuminate\Http\Request;


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
     * 请求分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function cate(){
        htmlHead();
        $cate = $this->categoryRepository
            ->select('id','name')
            ->get()->toArray();
        return response_success($cate);
    }
    /**
     * 从其他页面跳转到文章列表页面请求
     * @return mixed
     */
    private function right(){
        //查询所有分类
        $cates = $this->categoryRepository
            ->select('id','name')
            ->limit(20)
            ->get()->toArray();
        $articleRes['cates'] = $cates;
        //查询热点文章
        $articleRe = $this->articleRepository
            ->select('id','title')
            ->orderBy('like','desc')
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES)
            ->limit(8)
            ->get()->toArray();
        $articleRes['articleRe'] = $articleRe;
        //查询推荐文章
        $articleRec = $this->articleRepository
            ->select('id','title')
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('is_rec','=',Article::ISREC_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES)
            ->limit(8)
            ->get()->toArray();
        $articleRes['articleRec'] = $articleRec;
        return $articleRes;
    }
    /**
     * 分类显示对应的所有文章
     * @param $cateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,$cateId = '')
    {
        htmlHead();
        //查询有没有cateId传进来
        if($cateId){
            $cateId = (int)$cateId;
            $cateIdRes = $this->categoryRepository->find($cateId);
            if (!$cateIdRes){
                return response_failed('cate is not exists');
            }
        }
        //公用的查询
        $this->articleRepository = $this->articleRepository
            ->withCount(['Comment'])
            ->with(['UserInfo' => function($us){
                $us->select('id','name');
            }])
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES);
        //判断有没有cateId，如果没有，则认为是搜索功能
        if($cateId){
            $articleRes = $this->articleRepository
                ->where(['cate_id' => $cateId])
                ->orderBy('like','desc')
                ->paginate(6)->toArray();
            $articleRes['cate_name'] = $cateIdRes->toArray()['name'];
        }else{
            $seaRes = $request->get('search');
            $articleRes = $this->articleRepository
                ->where('title','like',"%{$seaRes}%")
                ->orwhere('desc','like',"%{$seaRes}%")
                ->orderBy('like','desc')
                ->paginate(6)->toArray();
            $articleRes['cate_name'] = '搜索中....';
        }
        //去除无用信息
        foreach ($articleRes['data'] as $k => $v){
            unset(
                $articleRes['data'][$k]['status'],
                $articleRes['data'][$k]['publish'],
                $articleRes['data'][$k]['created_at'],
                $articleRes['data'][$k]['is_rec'],
                $articleRes['data'][$k]['user_id']);
        }
        //从主页调转到文章列表请求一次
        if((int)$request->get('right') === 769){
            $res = $this->right();
            $articleRes['cates'] = $res['cates'];
            $articleRes['articleRe'] = $res['articleRe'];
            $articleRes['articleRec'] = $res['articleRec'];
        }
        //去除无用信息
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
        htmlHead();
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
        htmlHead();
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
            ->withCount(['Comment'])
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES)
            ->where(['id' => $id])
            ->first()->toArray();
            unset(
                $articleRes['user_id'],
                $articleRes['cate_id'],
                $articleRes['status'],
                $articleRes['publish'],
                $articleRes['created_at']
            );
        $articleRes['content'] = file_get_contents($articleRes['content']);
        return response_success($articleRes);
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
     * 作者更新文章
     * @param ArticleRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ArticleRequest $request,$id)
    {
        htmlHead();
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
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function del($id)
    {
        htmlHead();
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
