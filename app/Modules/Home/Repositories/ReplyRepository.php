<?php

namespace App\Modules\Home\Repositories;

use App\Modules\Basic\Repositories\BaseRepository;
use App\Modules\Home\Models\Comment;
use App\Modules\Home\Models\Reply;


class ReplyRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Reply::class;
    }


}