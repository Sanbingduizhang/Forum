<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Home\Repositories\CategoryRepository;
use App\Modules\Home\Repositories\CommentRepository;
use App\Modules\Home\Repositories\PhotoCateRepository;
use App\Modules\Home\Repositories\PhotoRepository;
use App\Modules\Home\Repositories\ReplyRepository;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PhotoController extends Controller
{
    //
    protected $photoRepository;
    protected $categoryRepository;
    protected $photoCateRepository;
    protected $commentRepository;
    protected $replyRepository;

    public function __construct(
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository,
        PhotoCateRepository $photoCateRepository,
        CommentRepository$commentRepository,
        ReplyRepository $replyRepository)
    {
        $this->photoRepository = $photoRepository;
        $this->categoryRepository = $categoryRepository;
        $this->photoCateRepository = $photoCateRepository;
        $this->commentRepository = $commentRepository;
        $this->replyRepository = $replyRepository;
    }

    /**
     * 显示所有相册(限制条件)
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
                $p->select('id','cate_id','img_path','img_name')->limit(1);
            }])
//            ->select('id','pname','use_id')
            ->where(['status' => 1,'share' => 1,'del' => 0])
            ->paginate(9)
            ->toArray();
        if(!$findRes){

            return response_success([]);
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
            ->where(['id' => $id,'status' => 1,'share' => 1,'del' => 0])
            ->first();
        if(!$cateRes) {
            return response_failed('数据有误');
        }
        $photoRes = $this->photoRepository
            ->with(['User' => function($u){
                $u->select('id','name');
            }])
            ->select('id','cate_id','userid','img_thumb','img_path','img_name','likecount','created_at')
            ->where(['cate_id' => $id])
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
        $returnArray['phototime'] = $cateRes->toArray()['created_at'];

        return response_success($returnArray);
    }

    /**
     * 单个图片的评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imgComment(Request $request)
    {
        htmlHead();
        $photoId = $request->get('photoid',null);
        $imgId = $request->get('imgid',null);
        //是否是直接显示评论（或者显示所有评论）
        $more = $request->get('more',false);
        if(empty($photoId) || empty($imgId)) {

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
                ->where(['cate' => 2,'article_id' => $imgId])
                ->toArray();
        } else {
            $photoComment = $this->commentRepository
                ->with(['UserInfo' => function($u){
                    $u->select('id','name');
                }])
                ->withCount(['Reply'])
                ->where(['cate' => 2,'article_id' => $imgId])
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
        $more = $request->get('more',false);
        if(empty($commentId) || empty($imgId)) {

            return response_failed('参数传递有误');
        }
        //'cate' => 2 代表图片评论
        //查询是否存在此图片
        $commentExist = $this->commentRepository
            ->where(['id' => $commentId,'article_id' => $imgId,'cate' => 2])
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
     * 显示单个图片的所有信息
     * @param $photoId
     * @param $imgId
     * @return \Illuminate\Http\JsonResponse
     * 暂不使用
     */
    public function showImg(Request $request)
    {
        htmlHead();
        $photoId = $request->get('photoid',null);
        $imgId = $request->get('imgid',null);
        if(empty($photoId) || empty($imgId)) {

            return response_failed('参数传递有误');
        }
        //判断是否存在数据
        $cateRes = $this->photoCateRepository
            ->where(['id' => $photoId,'status' => 1,'share' => 1,'del' => 0])
            ->first();
        //如果不存在
        if(!$cateRes) {
            return response_failed('数据有误');
        }
        //如果存在，则查询所有数据
        $photoRes = $this->photoRepository
            ->with(['User' => function($u){
                $u->select('id','name');
            }])
            ->select('id','cate_id','userid','img_path','img_name','likecount','created_at')
            ->where(['id' => $imgId,'cate_id' => $photoId])
            ->get()
            ->toArray();
        if(!$photoRes){

            return response_success([]);
        }
        //把相册名称，对应作者信息放上去
        $returnArray['res'] = $photoRes;
        $returnArray['photoname'] = $cateRes->pname;

        return response_success($returnArray);
    }

    /**
     * 上传单个图片
     * 暂时不启用
     * /photo/uploads/图片名称
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploads(Request $request,$id)
    {
        htmlHead();
        return false;
//        $arr = ['jpg','png','jpeg','gif'];
//        $photoRes = $this->photoCateRepository->where(['id' => $id,'use_id']);
//        if(!$photoRes){
//            return response_failed('分类有错误');
//        }
        $uploadRes = uploadsImg($request);
//        $option = ['jpg','png','jpeg','gif'];
//        $photoRes = $this->categoryRepository->where(['id' => $id,'cate' => 2]);
//        if(!$photoRes){
//            return response_failed('分类有错误');
//        }
//        //判断文件是否上传成功
//        if(!($request->hasFile('photo') && $request->file('photo'))){
//
//            return response_failed('Error in the process of uploading files or uploading');
//        }
//        //获取上传文件
//        $file = $request->file('photo');
//        $ext = strtolower($file->getClientOriginalExtension()); //文件扩展名
//        $originName = strtolower($file->getClientOriginalName());  //文件原名
//        //$type = $file->getClientMimeType();     // image/jpeg(真实文件名称)
//        //判断文件类型是否符合
//        if(!in_array($ext,$option)){
//
//            return response_failed('Please upload the specified type of picture:jpg,png,jpeg,gif');
//        }
//        //替换后的文件名称及路径
////        $course['img_path'] ? pathinfo($course['img_path'], PATHINFO_FILENAME) . '.' . $ext : '';
//        $path1 = date('YmdHis') . '-' . uniqid() . '.' . $ext;
//        $filesave = $file->storeAs('uploads', $path1,'uploads');
//        if(!$filesave) {
//            return response_failed('save is failed');
//        }
//        $path = '/photo/uploads/' . $path1;
//        $photoSave = $this->photoRepository->create([
//            'cate_id' => $id,
//            'userid' => 1,
//            'img_path' => $path,
//            'img_name' => $path1,
//            'img_origin' => $originName,
//            'img_ext' => $ext,
//        ]);
//        if(!$photoSave) {
//            return response_failed('save photo is failed');
//        }
//
//        return response_success(['message' => 'upload is successful!']);

    }
}
