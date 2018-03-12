<?php
namespace App\Modules\Basic\Support;

use Illuminate\Support\Facades\Cache;

class CacheRepo extends Cache {
    const KEY_JWT_USERINFO = 'JWT_USERINFO_';           // 通过JWT从平台获取的用户{jwt.uuid}的信息
    const KEY_SCHOOLDATA_BASE = 'SCHOOLDATA_BASE_';     // 从平台获取当前用户学校{school_id}的年级和科目数据
    const KEY_MSG_USER_UNREAD = 'MSG_USER_UNREAD_';     // 用户{jwt.uuid}的未读消息数
}