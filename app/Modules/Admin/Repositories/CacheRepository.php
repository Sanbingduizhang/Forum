<?php

namespace App\Modules\Admin\Repositories;

use App\Modules\Admin\Models\Cache;
use App\Modules\Basic\Repositories\BaseRepository;
use Illuminate\Http\Request;


class CacheRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Cache::class;
    }

}