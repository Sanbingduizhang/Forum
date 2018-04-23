<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Photo;
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
}