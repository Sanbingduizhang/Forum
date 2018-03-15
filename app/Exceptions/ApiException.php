<?php
/**
 * Created by PhpStorm.
 * User: WhiteYuan
 * Date: 2018/1/18
 * Time: 16:12
 */

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct($message, $code = -1) {
        parent::__construct($message, $code);
    }
}