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
        if($cateId){
            $cateId = (int)$cateId;
            $cateIdRes = $this->categoryRepository->find($cateId);
            if (!$cateIdRes){
                return response_failed('cate is not exists');
            }
        }

        $this->articleRepository = $this->articleRepository
            ->withCount(['Comment'])
            ->with(['UserInfo' => function($us){
                $us->select('id','name');
            }])
            ->where('status','=',Article::STATUS_ARTICLE_YES)
            ->where('publish','=',Article::PUBLISH_ARTICLE_YES);

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
                ->orwhere('title','like',"%{$seaRes}%")
                ->orderBy('like','desc')
                ->paginate(6)->toArray();
            $articleRes['cate_name'] = '搜索中....';
        }
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
//        $articleRes = unsetye($articleRes);
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
        htmlHead();
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
