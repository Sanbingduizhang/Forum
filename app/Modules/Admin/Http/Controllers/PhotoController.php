<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Admin\Http\Requests\PhotoCateRequest;
use App\Modules\Admin\Repositories\PhotoCateRepository;
use App\Modules\Admin\Repositories\PhotoRepository;
use App\Modules\Basic\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class PhotoController extends BaseController
{
    protected $photoCateRepository;
    protected $photoRepository;
    public function __construct(
        PhotoCateRepository $photoCateRepository,
        PhotoRepository $photoRepository)
    {
        $this->photoCateRepository = $photoCateRepository;
        $this->photoRepository = $photoRepository;
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
            ->where(['del' => 0,'use_id' => 1])
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
            ->where(['id' => $id,'del' => 0])
            ->first();
        if(!$cateRes) {
            return response_failed('数据有误');
        }
        $photoRes = $this->photoRepository
            ->with(['User' => function($u){
                $u->select('id','name');
            }])
            ->select('id','cate_id','userid','img_path','img_name')
            ->where(['cate_id' => $id,'userid' => 1])
            ->paginate(12)->toArray();
        if(!$photoRes) {
            return response_success([]);
        }
        $photoRes = unsetye($photoRes);
        return response_success($photoRes);
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
        if('' == $option['pname']) {
            return response_failed('相册名称不能为空');              //相册名称不为空
        }
        if(3 > mb_strlen($option['pname']) || 10 < mb_strlen($option['pname'])) {
            return response_failed('相册名称大于三个且小于10个');
        }
        $option['use_id'] = 1;
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
        $findRes = $this->photoCateRepository->where(['id' => $id,'use_id' => 1,'del' => 0])->first();
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
    public function delPhoto($id)
    {
        htmlHead();
        $id = (int)$id;
        $findRes = $this->photoCateRepository->where(['id' => $id,'use_id' => 1,'del' => 0])->first();
        if(!$findRes) {
            return response_failed('数据有误');
        }
        $delRes = $findRes->delete();
        if($delRes){
            return response_success(['message' => 'del successful']);
        }
        return response_failed('del failed');
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
        if(-1 == $uploadRes) {
            return response_failed('Please upload the specified type of picture:jpg,png,jpeg,gif');
        }
        if(-2 == $uploadRes) {
            return response_failed('save is failed');
        }
        //如果上传成功就进行数据插入
        $photoSave = $this->photoRepository->create([
            'cate_id' => $id,
            'userid' => 1,
            'img_path' => $uploadRes['path'],
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


    ///////////////////////////////-------------公用上传部分-------------//////////////////////////////////
}
