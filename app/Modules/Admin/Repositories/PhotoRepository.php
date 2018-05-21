<?php

namespace App\Modules\Admin\Repositories;


use App\Modules\Admin\Models\Photo;
use App\Modules\Basic\Repositories\BaseRepository;
use Illuminate\Http\Request;


class PhotoRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Photo::class;
    }

    /**
     * 相册添加过滤字段
     * @param Request $request
     * @return array
     */
    public function pNewCateRequest(Request $request)
    {
        $options = [
            'pname' => $request->get('name',''),
        ];
        return $options;
    }

    /**
     * 图片修改过滤字段
     * @param Request $request
     * @return array
     */
    public function pUpdateCateRequest(Request $request)
    {
        $options = [
            'name' => $request->get('name',''),
        ];
        return $options;
    }

    /**
     * 删除相册
     * @param Request $request
     * @return array
     */
    public function pDelRequest(Request $request)
    {
        $options = [
            'pIdArr' => $request->get('pIdArr',''),
            'pLength' => $request->get('pLength',''),
        ];
        return $options;
    }

    /**
     * 删除图片
     * @param Request $request
     * @return array
     */
    public function pDetailDelRequest(Request $request)
    {
        $options = [
          'cate' => (int)$request->get('cate',''),
          'photoid' => (int)$request->get('photoid',''),
          'imgIdArr' => $request->get('imgIdArr',''),
        ];
        return $options;
    }

}