<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Article;


class ArticleRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Article::class;
    }
}