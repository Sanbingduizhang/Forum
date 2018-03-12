<?php
/**
 * Created by PhpStorm.
 * User: WhiteYuan
 * Date: 2018/1/12
 * Time: 13:49
 */

namespace App\Modules\Basic\Support;

use Illuminate\Support\Facades\File;

class Helper {
    /**
     * @param $dir
     */
    public static function loadModuleHelpers($dir)
    {
        $helpers = File::glob($dir . '/../Helper/*.php');
        foreach ($helpers as $helper) {
            require_once $helper;
        }
    }
}