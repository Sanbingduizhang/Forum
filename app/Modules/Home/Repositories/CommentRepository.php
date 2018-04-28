<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Comment;


class CommentRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Comment::class;
    }


}