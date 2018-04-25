<?php


if(!function_exists('generateTree'))
{
    /**
     * @param $items
     * @param string $pid
     * @return array
     */
    function generateTree($items,$pid ="parent_id") {

        $map  = [];
        $tree = [];
        foreach ($items as &$it){ $map[$it['id']] = &$it; }  //数据的ID名生成新的引用索引树
        foreach ($items as &$it){
            $parent = &$map[$it[$pid]];
            if($parent) {
                $parent['children'][] = &$it;
            }else{
                $tree[] = &$it;
            }
        }
        return $tree;
    }
}
if(!function_exists('unsetye'))
{
    /**
     * @param $data
     * @return mixed
     */
    function unsetye($data) {
        unset(
            $data['first_page_url'],
            $data['from'],
            $data['last_page'],
            $data['last_page_url'],
            $data['path'],
            $data['prev_page_url'],
            $data['to'],
            $data['next_page_url']);
        return $data;
    }
}

if(!function_exists('htmlHead'))
{

    function htmlHead() {

//        return header("Access-Control-Allow-Origin:http://blog.heijiang.top");
        return header("Access-Control-Allow-Origin:*");
    }
}




if(!function_exists('response_success'))
{
    /**
     * @param array $params
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    function response_success(Array $params=[],$status='successful',$code=1)
    {
        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'data'=>$params
        ]);
    }
}

if(!function_exists('lastweek'))
{
    /**
     * 上周时间
     * @return array
     */
    function lastweek()
    {
        $lastWeekS =date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")-6,date("Y")));
        $lastWeekE =date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w"),date("Y")));
        $InTime = [$lastWeekS,$lastWeekE];
        return $InTime;
    }
}

if(!function_exists('response_failed')){
    /**
     * @param string $message
     * @param integer $code
     * @return \Illuminate\Http\JsonResponse
     */
    function response_failed($message='Response Failed',$code=-1)
    {
        return response()->json(['status'=>'failed','code'=>$code,'message'=>$message]);
    }
}



if(!function_exists('trans_arr')){
    /**
     * 语言包输入数组，合并输出
     */
    function trans_arr(Array $params,$delimiter='')
    {
        $trans_string = '';
        foreach($params as $param)
        {
            $trans_string .= trans($param) . $delimiter;
        }
        if($delimiter){
            return substr($trans_string,0,-(strlen($delimiter)));
        }
        return $trans_string;
    }
}

if (!function_exists('formatJson2Array'))
{
    /**
     * 格式化返回数据为数组
     *
     * @param $response_string
     * @return bool|mixed
     */
    function formatJson2Array($response_string)
    {
        $data = json_decode($response_string,TRUE);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        } else {
            return false;
        }
    }
}


if (!function_exists('load_module_helpers')) {
    /**
     * @param $dir
     */
    function load_module_helpers($dir)
    {
        App\Modules\Basic\Support\Helper::loadModuleHelpers($dir);
    }
}


if (!function_exists('uploadsImg')) {
    function uploadsImg($request,$arr)
    {
        $option = $arr;             //['jpg','png','jpeg','gif']
        //判断文件是否上传成功
        if(!($request->hasFile('photo') && $request->file('photo'))){

            return response_failed('Error in the process of uploading files or uploading');
        }
        //获取上传文件
        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension()); //文件扩展名
        $originName = strtolower($file->getClientOriginalName());  //文件原名
        $type = $file->getClientMimeType();     // image/jpeg(真实文件名称)
        //判断文件类型是否符合
        if(!in_array($ext,$option)){

            return  -1; //'Please upload the specified type of picture:jpg,png,jpeg,gif';
        }
        //替换后的文件名称及路径
//        $course['img_path'] ? pathinfo($course['img_path'], PATHINFO_FILENAME) . '.' . $ext : '';
        $path1 = date('YmdHis') . '-' . uniqid() . '.' . $ext;
        $filesave = $file->storeAs('uploads', $path1,'uploads');
        if(!$filesave) {
            return -2;   //'save is failed';
        }

        return $options = [
            'ext' => $ext,
            'originName' => $originName,
            'path' => 'http://photo.heijiang.top/uploads/' . $path1,
            'name' => $path1,
            'type' => $type,
        ];
    }
}
