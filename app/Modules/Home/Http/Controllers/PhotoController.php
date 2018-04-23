<?php

namespace App\Modules\Home\Http\Controllers;

use App\Modules\Home\Repositories\CategoryRepository;
use App\Modules\Home\Repositories\PhotoRepository;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PhotoController extends Controller
{
    //
    protected $photoRepository;
    protected $categoryRepository;
    public function __construct(
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository)
    {
        $this->photoRepository = $photoRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * 上传单个图片
     * /photo/uploads/图片名称
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    //根据图片
    public function uploads(Request $request,$id)
    {
        htmlHead();
        $option = ['jpg','png','jpeg','gif'];
        $photoRes = $this->categoryRepository->where(['id' => $id,'cate' => 2]);
        if(!$photoRes){
            return response_failed('分类有错误');
        }
        //判断文件是否上传成功
        if(!($request->hasFile('photo') && $request->file('photo'))){

            return response_failed('Error in the process of uploading files or uploading');
        }
        //获取上传文件
        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension()); //文件扩展名
        $originName = strtolower($file->getClientOriginalName());  //文件原名
        //$type = $file->getClientMimeType();     // image/jpeg(真实文件名称)
        //判断文件类型是否符合
        if(!in_array($ext,$option)){

            return response_failed('Please upload the specified type of picture:jpg,png,jpeg,gif');
        }
        //替换后的文件名称及路径
//        $course['img_path'] ? pathinfo($course['img_path'], PATHINFO_FILENAME) . '.' . $ext : '';
        $path1 = date('YmdHis') . '-' . uniqid() . '.' . $ext;
        $filesave = $file->storeAs('uploads', $path1,'uploads');
        if(!$filesave) {
            return response_failed('save is failed');
        }
        $path = '/photo/uploads/' . $path1;
        $photoSave = $this->photoRepository->create([
            'cate_id' => $id,
            'userid' => 1,
            'img_path' => $path,
            'img_name' => $path1,
            'img_origin' => $originName,
            'img_ext' => $ext,
        ]);
        if(!$photoSave) {
            return response_failed('save photo is failed');
        }
        
        return response_success(['message' => 'upload is successful!']);

    }
}
