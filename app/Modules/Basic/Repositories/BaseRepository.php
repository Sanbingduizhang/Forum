<?php
/**
 * Created by PhpStorm.
 * User: WhiteYuan
 * Date: 2018/1/12
 * Time: 13:47
 */

namespace App\Modules\Basic\Repositories;


use Bosnadev\Repositories\Eloquent\Repository;

abstract class BaseRepository extends Repository
{
    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $method
     * @param $argvments
     * @return mixed
     */
    public function __call($method,$argvments)
    {
        $this->applyCriteria();
        return call_user_func_array([$this->model,$method],$argvments);
    }


    /**
     * 简单的清空一维数组中空的key
     *
     * @param array $params
     * @return array
     */
    protected function clearNilData4Array(Array $params = [])
    {
        foreach($params as $key=>$value)
        {
            if(!$value) {
                unset($params[$key]);
            }
        }
        return $params;
    }
}