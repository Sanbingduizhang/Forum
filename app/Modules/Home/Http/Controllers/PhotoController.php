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
     * 上传图片
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    //根据课程名称命名上传的课程封面名称以及存储路径
    //暂时存储在storage/example下面，留待后期优化
    public function upload(Request $request,$id)
    {
        htmlHead();
        $option = ['jpg','png','jpeg','gif'];
        $photoRes = $this->categoryRepository->where(['id' => $id,'cate' => 2]);
        if(!$photoRes){
            return response_failed('分类有错误');
        }
        //判断文件是否上传成功
        if(!($request->hasFile('photo') && $request->file('photo')->isValid())){
            return response_failed('Error in the process of uploading files or uploading');
        }
        //获取上传文件
        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension()); //文件扩展名
        //判断文件类型是否符合
        if(!in_array($ext,$option)){
            return response_failed('Please upload the specified type of picture:jpg,png,jpeg,gif');
        }
        //替换后的文件名称及路径
//        $course['img_path'] ? pathinfo($course['img_path'], PATHINFO_FILENAME) . '.' . $ext : '';
        $path = date('YmdHis') . '-' . uniqid() . '.' . $ext;
        $path = $file->storeAs('example', $path);
//        //更新media_course中的img_path
//        $uploadImage = $this->courseRepo->update(['img_path'=>$path],$id);
//        if($uploadImage){
//            return response_success(['success' => 'Upload update successful!']);
//        }
//        return response_failed('Upload file failed, please reupload');
    }
}
