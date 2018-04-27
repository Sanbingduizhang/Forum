<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Home\Repositories\CategoryRepository;
use App\Modules\Home\Repositories\PhotoCateRepository;
use App\Modules\Home\Repositories\PhotoRepository;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PhotoController extends Controller
{
    //
    protected $photoRepository;
    protected $categoryRepository;
    protected $photoCateRepository;
    public function __construct(
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository,
        PhotoCateRepository $photoCateRepository)
    {
        $this->photoRepository = $photoRepository;
        $this->categoryRepository = $categoryRepository;
        $this->photoCateRepository = $photoCateRepository;
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
            ->select('id','cate_id','userid','img_path','img_name')
            ->where(['cate_id' => $id])
            ->paginate(12)
            ->toArray();
        if(!$photoRes){

            return response_success([]);
        }
        $returnArray['res'] = unsetye($photoRes);
        $returnArray['username'] = $cateRes->User->name;
        $returnArray['userimg'] = $cateRes->User->img_path;
        $returnArray['photoname'] = $cateRes->pname;

        return response_success($returnArray);
    }
    /**
     * 上传单个图片
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
