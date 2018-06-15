<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Home\Repositories\ArticleRepository;
use App\Modules\Home\Repositories\CommentRepository;
use App\Modules\Home\Repositories\LikeCountRepository;
use App\Modules\Home\Repositories\PhotoRepository;
use App\Modules\Home\Repositories\ReplyRepository;
use Illuminate\Http\Request;


class CommentController extends BaseController
{
    protected $commentRepository;
    protected $articleRepository;
    protected $photoRepository;
    protected $replyRepository;
    protected $likeCountRepository;
    public function __construct(
        CommentRepository $commentRepository,
        ArticleRepository $articleRepository,
        PhotoRepository $photoRepository,
        ReplyRepository $replyRepository,
        LikeCountRepository $likeCountRepository

    )
    {

        $this->commentRepository = $commentRepository;
        $this->articleRepository = $articleRepository;
        $this->photoRepository = $photoRepository;
        $this->replyRepository = $replyRepository;
        $this->likeCountRepository = $likeCountRepository;
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
        $userid = $request->get('uid'); //获取用户id
        //$userid = 2;    //获取用户的id，暂时写定
        $options = $this->commentRepository->addComRequest($request);
        if ('' == trim($options['content']) || '' == $options['article_id'] || '' == $options['cate']) {
            return response_failed('参数传递错误');
        }
        $options['user_id'] = $userid;
        //处理数据信息
        if (1 == $options['cate']) {
            $findRes = $this->articleRepository
                ->where(['id' => $options['article_id']])->first();
        } elseif(2 == $options['cate']) {
            $findRes = $this->photoRepository
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
    public function comDel(Request $request,$id,$cate)
    {
        htmlHead();
        $userid = $request->get('uid'); //获取用户id
//        $userid = 2;
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
        $delRepFind = $this->replyRepository
            ->where(['comment_id' => $id])
            ->first();
        if(!$delRepFind) {
            return response_success(['message' => '删除成功']);
        }
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
        $userid = $request->get('uid'); //获取用户id
//        $userid = 2;
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
        $userid = $request->get('uid'); //获取用户id
//        $userid = 2;
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
        $userid = $request->get('uid'); //获取用户id
//        $userid = 2;
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

    /**
     * 文章或者图片下方的评论或者回复的点赞或者取消赞
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function LikeChildGo(Request $request)
    {
        htmlHead();
        $userid = $request->get('uid'); //获取用户id
//        $userid = 2;
        $options = $this->likeCountRepository->LikeCountRequest($request);
        $options['user_id'] = $userid;
        //判断参数是否正确
        if (0 == $options['article_id'] || 0 == $options['comment_id']) {
            return response_failed('参数错误');
        }
        //判断是评论点赞还是回复点赞
        if (0 == $options['reply_id']) {
            $findRes = $this->commentRepository
                ->where(['id' => $options['comment_id']])
                ->first();
        } else {
            $findRes = $this->replyRepository
                ->where(['comment_id' => $options['comment_id'],'id' => $options['reply_id']])
                ->first();
        }
        //如果查询不到，则返回错误
        if (!$findRes) {
            return response_failed('数据有误');
        }
        //修改评论或者回复的点赞数量
        //如果$options['like'] == 0 则认为是取消点赞
        if (0 == $options['like']) {
            if (0 == $options['reply_id']) {
                $likeRes = $this->commentRepository
                    ->where(['id' => $options['comment_id']])
                    ->update(['likecount' => $findRes['likecount'] - 1]);
            } else {
                $likeRes = $this->commentRepository
                    ->where(['id' => $options['reply_id']])
                    ->update(['likecount' => $findRes['likecount'] - 1]);
            }
            if (!$likeRes) {
                return response_failed('取消赞失败!');
            }
            //如果修改成功，则点赞表中数量删除一条数量
            $likeDel = $this->likeCountRepository
                ->where(['id' => $options['id'],'user_id' => $userid])
                ->delete();
            if (!$likeDel) {
                return response_failed('取消赞失败');
            }
            return response_success(['message' => '取消赞成功']);

        } else {
            if (0 == $options['reply_id']) {
                $likeRes = $this->commentRepository
                    ->where(['id' => $options['comment_id']])
                    ->update(['likecount' => $findRes['likecount'] + 1]);
            } else {
                $likeRes = $this->commentRepository
                    ->where(['id' => $options['reply_id']])
                    ->update(['likecount' => $findRes['likecount'] + 1]);
            }
            if (!$likeRes) {
                return response_failed('点赞失败!');
            }
            //如果修改成功，则点赞表中数量增加一条数量
            $likeAdd = $this->likeCountRepository
                ->create($options);
            if (!$likeAdd) {
                return response_failed('点赞失败');
            }
            return response_success(['message' => '点赞成功']);
        }

    }

    public function LikeGo(Request $request)
    {
        htmlHead();
        //获取文章或者图片的id以及是否点赞
        $options = $this->likeCountRepository->LikeCountParRequest($request);
        //获取当前登陆的用户信息
        $options['user_id'] = $request->get('uid');
        //判断数据是否为空
        if (0 == $options['article_id']) {
            return response_failed('数据错误');
        }
        //查询对应的数据是否存在
        if (1 == $options['cate']) {
            $findRes = $this->articleRepository->where(['id' => $options['article_id']])->first();
        } else {
            $findRes = $this->photoRepository->where(['id' => $options['article_id']])->first();
        }
        if (!$findRes) {
            return response_failed('数据不存在');
        }
        //数据填充
        //如果id为0.则是点赞，否则是取消赞
        if (0 == $options['id']) {
            //相对应的文章或者而图片点赞数修改
            $findRes->like = $findRes->like + 1;
            $saveRes = $findRes->save();
            if (!$saveRes) {
                return response_failed('点赞失败');
            }
            //对应的点赞表中的数据修改
            $saveLike = $this->likeCountRepository->create($options);
        } else {
            $findRes->like = $findRes->like - 1;
            $saveRes = $findRes->save();
            if (!$saveRes) {
                return response_failed('点赞失败');
            }
            $saveLike = $this->likeCountRepository->find($options['id'])->delete();
        }
        if ($saveLike) {
            return response_success(['message' => '操作成功']);
        }
        return response_failed('操作失败');
    }
}
