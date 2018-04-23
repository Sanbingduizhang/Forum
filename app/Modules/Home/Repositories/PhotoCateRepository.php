<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\PhotoCate;


class PhotoCateRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return PhotoCate::class;
    }
}