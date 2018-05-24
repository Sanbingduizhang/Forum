<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Admin\Http\Requests\PhotoCateRequest;
use App\Modules\Admin\Repositories\PhotoCateRepository;
use App\Modules\Admin\Repositories\PhotoRepository;
use App\Modules\Basic\Http\Controllers\BaseController;
use App\Modules\Basic\Providers\ImgCompress;
use App\Modules\Home\Repositories\CommentRepository;
use App\Modules\Home\Repositories\ReplyRepository;
use Illuminate\Http\Request;

class PhotoController extends BaseController
{
    protected $photoCateRepository;
    protected $photoRepository;
    protected $commentRepository;
    protected $replyRepository;
    public function __construct(
        PhotoCateRepository $photoCateRepository,
        PhotoRepository $photoRepository,
        CommentRepository $commentRepository,
        ReplyRepository $replyRepository)
    {
        $this->photoCateRepository = $photoCateRepository;
        $this->photoRepository = $photoRepository;
        $this->commentRepository = $commentRepository;
        $this->replyRepository = $replyRepository;
    }
    ///////////////////////////////-------------后台相册编辑部分-------------//////////////////////////////////
    /**
     * 显示所有相册
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        htmlHead();
        $findRes = $this->photoCateRepository
            ->with(['User' => function($u){
                $u->select('id','name');
            }])
            ->withCount(['Photo'])
            ->with(['Photo' => function($p){
                $p->select('id','cate_id','img_path','img_name');
            }])
//            ->select('id','pname','use_id')
            ->where(['del' => 0,'use_id' => 2])
            ->orderBy('created_at','desc')
            ->paginate(8)
            ->toArray();
        if(!$findRes){

            return response_success([]);
        }
        foreach ($findRes['data'] as $k => $v) {
            if ($v['photo']) {
                $findRes['data'][$k]['photo'] = $v['photo'][0];
            }
        }
        $findRes = unsetye($findRes);
        return response_success($findRes);
    }

    /**
     * 获取单个相册所有图片
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        htmlHead();
        $cateRes = $this->photoCateRepository
            ->where(['id' => $id,'status' => 1,'del' => 0])
            ->first();
        if(!$cateRes) {
            return response_failed('数据有误');
        }
        $photoRes = $this->photoRepository
            ->with(['User' => function($u){
                $u->select('id','name');
            }])
            ->select('id','cate_id','userid','img_thumb','img_path','img_name','likecount','created_at')
            ->where(['cate_id' => $id,'userid' => 2])
            ->paginate(12)
            ->toArray();
        if(!$photoRes){

            return response_success([]);
        }
        //把相册名称，对应作者信息放上去
        $returnArray['res'] = unsetye($photoRes);
        $returnArray['username'] = $cateRes->User->name;
        $returnArray['userimg'] = $cateRes->User->img_path;
        $returnArray['photoname'] = $cateRes->pname;
        $returnArray['cate'] = 2;   //图片
        $returnArray['photoid'] = $id;   //当前相册id
        $returnArray['phototime'] = $cateRes->toArray()['created_at'];

        return response_success($returnArray);
    }

    /**
     * 相册创建
     * @param PhotoCateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPhoto(PhotoCateRequest $request)
    {
        htmlHead();
        $option = $this->photoCateRepository->pNewCateRequest($request);
        $option['use_id'] = 2;
        if('' == $option['pname']) {
            return response_failed('相册名称不能为空');              //相册名称不为空
        }
        if(3 > mb_strlen($option['pname']) || 10 < mb_strlen($option['pname'])) {
            return response_failed('相册名称大于三个且小于10个');
        }
        //判断相册名称是否存在
        $findRes = $this->photoCateRepository
            ->where(['use_id' => $option['use_id'],'pname' => $option['pname']])
            ->first();

        if ($findRes) {
            return response_failed('相册名称已经存在');
        }
        //不存在，则进行添加相册
        $createRes = $this->photoCateRepository->create($option);
        if($createRes){
            return response_success(['message' => 'add successful']);    //添加成功
        }
        return response_failed('add falied');    //添加失败
    }

    /**
     * 修改相册
     * @param PhotoCateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhoto(PhotoCateRequest $request,$id)
    {
        htmlHead();
        $option = $this->photoCateRepository->pNewCateRequest($request);
        if('' == $option['pname']) {
            return response_failed('相册名称不能为空');              //相册名称不为空
        }
        $findRes = $this->photoCateRepository->where(['id' => $id,'use_id' => 2,'del' => 0])->first();
        if(!$findRes) {
            return response_failed('数据有误');
        }
        $findRes->pname = $option['pname'];
        $saveRes = $findRes->save();
        if($saveRes){
            return response_success(['message' => 'update is successful']);
        }
        return response_failed('update is failed');
    }

    /**
     * 删除相册（暂不使用）
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delPhoto(Request $request)
    {
        htmlHead();
        $userid = 2;
        $options = $this->photoRepository->pDelRequest($request);
        if ('' == $options['pIdArr'] || '' == $options['pLength']) {
            return response_failed('参数错误');
        }
        if (!is_array($options['pIdArr'])) {
            return response_failed('参数错误');
        }
        //查找是否存在本人相册
        $findRes = $this->photoCateRepository
            ->whereIn('id',$options['pIdArr'])
            ->where(['use_id' => $userid,'del' => 0]);
        //判断相册选择数量是否正确
        if ($options['pLength'] != $findRes->count()) {
            return response_failed('数据存在不符');
        }
        //删除相册
        $delRes = $findRes->delete();
        if(!$delRes){
            return response_failed('del failed');
        }
        //删除所有相册中的所有图片
        $pDetailFindRes = $this->photoRepository
            ->whereIn('cate_id',$options['pIdArr'])
            ->where(['userid' => $userid]);
        if (!$pDetailFindRes->count()) {
            return response_success(['message' => 'del photo successful']);
        }
        //查找所有图片的id，所有图片的名称
        $pDetailFindAll = $pDetailFindRes->select('id','img_name')->get()->toArray();
        $pDetailFindids = array_column($pDetailFindAll,'id');
        $pDetailFindimgs = array_column($pDetailFindAll,'img_name');
        //删除所有相册中的所有图片
        $pDetail = $pDetailFindRes->delete();
        if(!$pDetailFindAll){
            if(!$pDetail){
                return response_failed('del pDetail failed');
            }
        }

        $delAll = $this->delAllCon($pDetailFindimgs,$pDetailFindids);

        if($delAll){
            return response_success(['message' => 'del photo and pDetail successful']);
        }
        return response_failed('del pDetail failed');
    }

    /**
     * 图片的单个或者批量删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delPDetail(Request $request)
    {
        htmlHead();
        $userid = 2;
        $options = $this->photoRepository->pDetailDelRequest($request);
//        var_dump($options);exit();
        if ('' == $options['photoid'] || '' == $options['imgIdArr']) {
           return response_failed('参数错误');
        }
        if (!is_array($options['imgIdArr'])) {
            return response_failed('参数错误');
        }
        $findRes = $this->photoCateRepository
            ->where(['id' => $options['photoid'],'use_id' => $userid])
            ->first();
        if (!$findRes) {
           return response_failed('数据有误');
        }
        //查找图片名称
        $imgNameRes = $this->photoRepository
            ->select('img_name')
            ->where(['userid' => $userid])
            ->whereIn('id',$options['imgIdArr'])
            ->get()->toArray();
        //删除图片
        $delRes = $this->photoRepository->destroy($options['imgIdArr']);
        if(!$imgNameRes){
            if(!$delRes){
                return response_failed('删除失败');
            }
        }
        $imgNameRes = array_column($imgNameRes,'img_name');
        $delAll = $this->delAllCon($imgNameRes,$options['imgIdArr']);
        //删除对应图片下面的所有回复
        if ($delAll) {
           return response_success(['message' => '删除成功']);
        }
        return response_failed('删除失败');

    }

    /**
     * 上传图片
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImg(Request $request,$id)
    {
        htmlHead();
        $arr = ['jpg','png','jpeg','gif'];
        $photoRes = $this->photoCateRepository->where(['id' => $id,'use_id' => 2,'del' => 0]);
        if(!$photoRes){
            return response_failed('分类有错误');
        }
        //上传文件
        $uploadRes = uploadsImg($request,$arr);
        //判断结果
        if (-1 == $uploadRes) {
            return response_failed('Please upload the specified type of picture:jpg,png,jpeg,gif');
        }
        if (-2 == $uploadRes) {
            return response_failed('save is failed');
        }
        if (-3 == $uploadRes) {
            return response_failed('Error in the process of uploading files or uploading');
        }
        //进行图片压缩
        image_size_add("/photo/uploads/".$uploadRes['name'],"/photo/small/".$uploadRes['name']);
//        $thumbPaths = thumbImage(
//            "/photo/uploads/".$uploadRes['name'],
//            $uploadRes['ext'],
//            960,
//            640,
//            "/photo/small",
//            false
//        );
        //如果上传成功就进行数据插入
        $photoSave = $this->photoRepository->create([
            'cate_id' => $id,
            'userid' => 2,
            'img_path' => $uploadRes['path'],
            'img_thumb' => "http://photo.heijiang.top/small/".$uploadRes['name'],
//            'img_thumb' => $uploadRes['path'],
            'img_name' => $uploadRes['name'],
            'img_origin' => $uploadRes['originName'],
            'ext' => $uploadRes['ext'],
            'type' => $uploadRes['type'],
        ]);
        if(!$photoSave) {
            return response_failed('save photo is failed');
        }

        return response_success(['message' => 'upload is successful!']);
    }
    ///////////////////////////////-------------公用文章部分-------------//////////////////////////////////

    /**
     * 删除服务器图片，删除对应图片下方的所有评论，所有回复（支持批量删除）
     * @param $imgNameRes array 图片名称
     * @param $imgIdArr array   图片的id
     * @return bool
     */
    public function delAllCon($imgNameRes,$imgIdArr)
    {
        //删除服务器上面的图片
        foreach ($imgNameRes as $k => $v) {
            unlink("/photo/uploads/" . $v);
            unlink("/photo/small/" . $v);
        }
        //删除图片下面的评论
        $imgComres = $this->commentRepository
            ->whereIn('article_id',$imgIdArr);
        if(0 == $imgComres->count()) {
            return true;
        }

        //获取对应图片下方回复
        $imgComIds = $imgComres->select('id')->get()->toArray();
        //删除评论
        $imgComres->delete();
        if(!$imgComIds) {
            return true;
        }
        //整合评论id
        $imgComIds = array_column($imgComIds,'id');
        //删除回复
        $imgRepRes = $this->replyRepository
            ->whereIn('comment_id',$imgComIds);
        if(0 == $imgRepRes->count()){
            return true;
        }
        $imgRepRes->delete();
        return true;
    }

    ///////////////////////////////-------------公用上传部分-------------//////////////////////////////////
}
