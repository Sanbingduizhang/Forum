<?php

namespace App\Modules\Admin\Repositories;


use App\Modules\Admin\Models\PhotoCate;
use App\Modules\Basic\Repositories\BaseRepository;
use Illuminate\Http\Request;


class PhotoCateRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return PhotoCate::class;
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

}