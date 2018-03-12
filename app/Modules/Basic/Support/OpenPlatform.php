<?php
/**
 * Created by PhpStorm.
 * User: WhiteYuan
 * Date: 2018/1/13
 * Time: 13:30
 */

namespace App\Modules\Basic\Support;
use GuzzleHttp\Client;
use App\Modules\Basic\Support\CacheRepo as Cache;

class OpenPlatform
{
    protected $base_uri;
    protected $uuid = null;
    protected $timestamp;
    protected $headers = [];

    /**
     * @var string
     */
    private $edutech_sign;

    /**
     * @var string
     */
    private $edutech_entity;

    /**
     * OpenPlatformSupport constructor.
     */
    public function __construct()
    {
        $this->timestamp = time();
        $this->base_uri = config('platform.base_url');
    }

    /**
     * @param $base_uri
     * @return $this
     */
    public function setBaseUrl($base_uri){
        $this->base_uri = $base_uri;
        return $this;
    }

    /**
     * @param $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     *
     * timestamp+sign
     *
     * 生成 X-Edutech-Sign
     *
     * @param $sign
     * @return $this
     */
    protected function make_edutech_sign($sign)
    {
        $this->edutech_sign = $this->timestamp .'+'. $sign;
        return $this;
    }


    /**
     * 生成header x-edutech-entity
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function make_edutech_entity()
    {
        if($this->uuid==null) {
            throw new \InvalidArgumentException('uuid may not be empty');
        }
        $this->edutech_entity = $this->uuid;
        return $this;
    }

    /**
     * @param $sign
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function set_headers($sign)
    {
        $this->make_edutech_entity()->make_edutech_sign($sign);
        $this->headers = [
            'X-Edutech-Entity'=>$this->edutech_entity,
            'X-Edutech-Sign'=>$this->edutech_sign
        ];
        return $this;
    }


    /**
     * @param array $params
     * @return Client
     */
    private function httpClient(Array $params=[])
    {
        if($this->base_uri!=null)
        {
            $params['base_uri'] = $this->base_uri;
            $params['headers'] = $this->headers;
        }
        return new Client($params);
    }

    /**
     * 产生可以使用的地址
     *
     * @param $url
     * @param $params
     * @return string
     */
    private function generate_url_param($url,$params,$nocache=true)
    {
        if($nocache==true)
        {
            $params['_'] = time();
        }
        return $url . (str_contains($url,'?') ? '&' : '?' ) . http_build_query($params);
    }

    /**
     * 格式化返回数据为数组
     *
     * @param $response_string
     * @return bool|mixed
     */
    private function formatJson2Array($response_string)
    {
        $data = json_decode($response_string,TRUE);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        } else {
            return false;
        }
    }


    /**
     * 重置get方法
     *
     * @param $url
     * @param array $params
     * @return bool|mixed
     */
    private function get($url,$params=[])
    {
        $request_uri = $this->generate_url_param($url,$params);
        $response = $this->httpClient($params)->get($request_uri)->getBody();
        return $this->formatJson2Array($response);
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function user_info(){
        $sign = md5($this->timestamp . $this->uuid);
        $response = $this->set_headers($sign)->get(config('platform.user_info'));

        $result_data = $response['data']['user'];
        $result_data['privatekey'] = $response['data']['privatekey'];

        $school = $result_data['school'][0];unset($result_data['school']);
        $result_data['school_id'] = $school['id'];
        $result_data['school_name'] = $school['name'];

        switch ($result_data['type']) {
            case '01':
                $result_data['identity'] = 'admin';
                break;
            case '02':
                $result_data['identity'] = 'teacher';
                break;
            case '03':
                $result_data['identity'] = 'student';
                break;
            default:
                $result_data['identity'] = 'guest';
                break;
        }
        return $result_data;
    }

    /**
     * 获取教学平台上的学校年级和科目数据 (外部调用的统一方法，优先从缓存获取)
     * @author wangdi
     * @param array $currentUser 当前用户信息
     * @return array [grades, subjects]
     */
    public function get_school_base_data(Array $currentUser)
    {
        $cacheKey = Cache::KEY_SCHOOLDATA_BASE . $currentUser['school_id'];
        $data = Cache::get($cacheKey);
        if (!is_array($data) || empty($data)) {
            try {
                $data = $this->setUuid($currentUser['usercode'])
                    ->school_base_data($currentUser['privatekey']);
            } catch (Exception $e) {
                return response_failed($e->getMessage());
            }
        }
        Cache::put($cacheKey, $data, 30);

        return $data;
    }

    /**
     * 从教学平台读取学校年级和科目数据
     * @author wangdi
     * @param string $schoolPrivateKey
     * @return array [grades, subjects]
     */
    private function school_base_data($schoolPrivateKey)
    {
        // 修改请求头部
        $this->headers = [
            'X-Edutech-Entity'=> $this->uuid,
            'X-Edutech-Sign'=> $this->timestamp . '+' . md5($this->timestamp . $this->uuid . $schoolPrivateKey)
        ];

        // 获取年级数据
        $url = config('platform.happyclass_url') . config('platform.get_happyclass_grades');
        $data = $this->get($url);
        $data = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];
        $grades = [];
        if (!empty($data)) {
            foreach ($data as $row) {
                $grades[] = [
                    'id' => (int) $row['GradeID'],
                    'name' => $row['GradeAlias'],
                ];
            }
        }

        // 获取科目数据
        $url = config('platform.happyclass_url') . config('platform.get_happyclass_subjects');
        $data = $this->get($url, ['type' => 'all']);
        $subjects = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];

        // 返回数据
        return [
            'grades' => $grades,
            'subjects' => $subjects,
        ];
    }
}