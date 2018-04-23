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

}