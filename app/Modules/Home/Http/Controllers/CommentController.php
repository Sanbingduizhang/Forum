<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CommentRepository;
use App\Modules\Home\Repositories\PhotoRepository;
use App\Modules\Home\Repositories\ReplyRepository;
use Illuminate\Http\Request;


class CommentController extends BaseController
{
    protected $commentRepository;
    protected $articleRepository;
    protected $photoRepository;
    protected $replyRepository;
    public function __construct(
        CommentRepository $commentRepository,
        ArticleRepository $articleRepository,
        PhotoRepository $photoRepository,
        ReplyRepository $replyRepository

    )
    {

        $this->commentRepository = $commentRepository;
        $this->articleRepository = $articleRepository;
        $this->photoRepository = $photoRepository;
        $this->replyRepository = $replyRepository;
    }
    /**
     * 单个图片的评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imgComment(Request $request)
    {
        htmlHead();
        $photoId = (int)$request->get('photoid',null);
        $imgId = (int)$request->get('imgid',null);
        $cate = (int)$request->get('cate',null);
        //是否是直接显示评论（或者显示所有评论）
        $more = $request->get('more',false);
        if(empty($photoId) || empty($imgId) || empty($cate)) {

            return response_failed('参数传递有误');
        }
        //'cate' => 2 代表图片评论
        //查询是否存在此图片
        $photoExist = $this->photoRepository
            ->where(['id' => $imgId,'cate_id' => $photoId])
            ->first();
        if(!$photoExist) {

            return response_failed('数据有误');
        }
        //如果传递more，则认为查询所有评论数据（一次性返回，否则，只显示四条评论）
        if($more){
            $photoComment = $this->commentRepository
                ->with(['UserInfo' => function($u){
                    $u->select('id','name');
                }])
                ->withCount(['Reply'])
                ->where(['cate' => $cate,'article_id' => $imgId])
                ->orderBy('likecount','desc')
                ->orderBy('created_at','desc')
                ->get()
                ->toArray();
        } else {
            $photoComment = $this->commentRepository
                ->with(['UserInfo' => function($u){
                    $u->select('id','name');
                }])
                ->withCount(['Reply'])
                ->where(['cate' => $cate,'article_id' => $imgId])
                ->orderBy('likecount','desc')
                ->orderBy('created_at','desc')
                ->paginate(5)
                ->toArray();
        }
        //如果查询不到,则返回空数组
        if(!$photoComment) {

            return response_success([]);
        }
        //去除分页多余信息
        $photoComment = unsetye($photoComment);

        return response_success($photoComment);
    }

    /**
     * 单个图片的某条评论下方的所有回复
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imgReply(Request $request)
    {
        htmlHead();
        $imgId = $request->get('imgid',null);
        $commentId = $request->get('commentid',null);
        $cate = $request->get('cate',null);

        $more = $request->get('more',false);
        if(empty($commentId) || empty($imgId) || empty($cate)) {

            return response_failed('参数传递有误');
        }
        //'cate' => 2 代表图片评论
        //查询是否存在此图片
        $commentExist = $this->commentRepository
            ->where(['id' => $commentId,'article_id' => $imgId,'cate' => $cate])
            ->first();
        if(!$commentExist) {

            return response_failed('数据有误');
        }
        //如果传递more为true，则认为是查询所有回复
        if($more){
            //如果存在，则查询评论对应的所有回复
            $replyDatas = $this->replyRepository
                ->where(['comment_id' => $commentId])
                ->get()
                ->toArray();
        } else{
            $replyDatas = $this->replyRepository
                ->where(['comment_id' => $commentId])
                ->paginate(3)
                ->toArray();
        }

        if(!$replyDatas) {

            return response_success([]);
        }

        return response_success($replyDatas);

    }

    /**
     * 添加评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comAdd(Request $request)
    {
        htmlHead();
        $userid = 2;    //获取用户的id，暂时写定
        $options = $this->commentRepository->addComRequest($request);
        if ('' == trim($options['content']) || '' == $options['article_id'] || '' == $options['cate']) {
            return response_failed('参数传递错误');
        }
        $options['user_id'] = $userid;
        //处理数据信息
        if (1 == $options['cate']) {
            $findRes = $this->photoRepository
                ->where(['id' => $options['article_id']])->first();
        } elseif(2 == $options['cate']) {
            $findRes = $this->articleRepository
                ->where(['id' => $options['article_id']])->first();
        } else {
            $findRes = [];
        }
        //如果查找不到，则直接返回
        if (!$findRes) {
            return response_failed('数据有误');
        }
        //进行评论插入
        $insertRes = $this->commentRepository->create($options);
        if($insertRes){
            return response_success(['message' => '评论成功']);
        }
        return response_failed('评论失败');
    }

    /**
     * 删除评论
     * @param $id
     * @param $cate
     * @return \Illuminate\Http\JsonResponse
     */
    public function comDel($id,$cate)
    {
        htmlHead();
        $userid = 2;
        //查找是否存在此条评论
        $findCom = $this->commentRepository
            ->where(['user_id' => $userid,'cate' => $cate,'id' => $id])
            ->first();
        if(!$findCom) {
            return response_failed('数据有误');
        }
        //删除评论
        $delCom = $findCom->delete($id);
        if(!$delCom) {
            return response_failed('删除失败');
        }
        //删除评论下方的所有回复
        $delRep = $this->replyRepository
            ->where(['comment_id' => $id])
            ->delete();
        if($delRep){
            return response_success(['message' => '删除成功']);
        }
        return response_failed('删除失败');
    }

    /**
     * 评论的回复
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comRepAdd(Request $request)
    {
        htmlHead();
        $userid = 2;
        $options = $this->replyRepository->addComRepRequest($request);
        if('' == $options['content'] || '' == $options['comment_id']) {

            return response_failed('参数有误');
        }
        $options['user_id'] = $userid;
        //查询是否存在这条评论
        $findCom = $this->commentRepository
            ->where(['id' => $options['comment_id']])
            ->first();
        if (!$findCom) {
            return response_failed('数据有误');
        }
        //如果存在，则开始回复
        $repSave = $this->replyRepository
            ->create($options);
        if($repSave){
            return response_success(['message' => '添加回复成功']);
        }
        return response_failed('添加回复失败');
    }

    /**
     * 回复的回复
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function repRepAdd(Request $request)
    {
        htmlHead();
        $userid = 2;
        $options = $this->replyRepository->addRepRepRequest($request);
        if('' == $options['content'] || '' == $options['comment_id'] || 0 == $options['pid']) {

            return response_failed('参数有误');
        }
        $options['user_id'] = $userid;
        //查询是否存在这条评论
        $findRep = $this->commentRepository
            ->where(['id' => $options['pid'],'comment_id' => $options['comment_id']])
            ->first();
        if (!$findRep) {
            return response_failed('数据有误');
        }
        //如果存在，则开始回复
        $repSave = $this->replyRepository
            ->create($options);
        if($repSave){
            return response_success(['message' => '添加回复成功']);
        }
        return response_failed('添加回复失败');

    }

    /**
     * 回复的删除
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function repDel(Request $request,$id)
    {
        htmlHead();
        $userid = 2;
        $id = (int)$id;
        if(!isset($id) && empty($id)) {

            return response_failed('参数有误');
        }
        //查询是否存在这条评论
        $findRep = $this->commentRepository
            ->where(['id' => $id,'user_id' => $userid])
            ->first();
        if (!$findRep) {
            return response_failed('数据有误');
        }
        //如果存在，则开始回复
        $repDel = $findRep->delete();
        if($repDel){
            return response_success(['message' => '删除回复成功']);
        }
        return response_failed('删除回复失败');

    }
}
