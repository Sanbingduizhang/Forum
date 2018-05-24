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

            return -3;  //Error in the process of uploading files or uploading
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

/**
 * 制作缩略图
 * @param $src_path string 原图路径
 * @param $ext string 后缀
 * @param $max_w int 画布的宽度
 * @param $max_h int 画布的高度
 * @param $foldername string 目标路径
 * @param $flag bool 是否是等比缩略图  默认为false
 */
if (!function_exists('thumbImage')) {
    function thumbImage($src_path,$ext,$max_w,$max_h,$foldername,$flag = true)
    {
        //获取文件的后缀
//        $ext=  strtolower(strrchr($src_path,'.'));
        //判断文件格式
        switch($ext){
            case 'jpg':
                $type='jpeg';
                break;
            case 'gif':
                $type='gif';
                break;
            case 'png':
                $type='png';
                break;
            default:
                $this->error='文件格式不正确';
                return false;
        }

        //拼接打开图片的函数
        $open_fn = 'imagecreatefrom'.$type;
        //打开源图
        $src = $open_fn($src_path);
        //创建目标图
        $dst = imagecreatetruecolor($max_w,$max_h);

        //源图的宽
        $src_w = imagesx($src);
        //源图的高
        $src_h = imagesy($src);

        //是否等比缩放
        if ($flag) { //等比

            //求目标图片的宽高
            if ($max_w/$max_h < $src_w/$src_h) {

                //横屏图片以宽为标准
                $dst_w = $max_w;
                $dst_h = $max_w * $src_h/$src_w;
            }else{

                //竖屏图片以高为标准
                $dst_h = $max_h;
                $dst_w = $max_h * $src_w/$src_h;
            }
            //在目标图上显示的位置
            $dst_x=(int)(($max_w-$dst_w)/2);
            $dst_y=(int)(($max_h-$dst_h)/2);
        }else{    //不等比

            $dst_x=0;
            $dst_y=0;
            $dst_w=$max_w;
            $dst_h=$max_h;
        }

        //生成缩略图
        imagecopyresampled($dst,$src,$dst_x,$dst_y,0,0,$dst_w,$dst_h,$src_w,$src_h);

        //文件名
        $filename = basename($src_path);
        //文件夹名
//        $foldername=substr(dirname($src_path),0);
        //缩略图存放路径
        $thumb_path = $foldername.'/'.$filename;

        //把缩略图上传到指定的文件夹
        imagepng($dst,$thumb_path);
        //销毁图片资源
        imagedestroy($dst);
        imagedestroy($src);

        //返回新的缩略图的文件名
        return [
            'thumb_path' => $thumb_path,
            'thumb_name' => $filename,
        ];
    }


    /**
     * desription 压缩图片
     * @param string $imgsrc 图片路径
     * @param string $imgdst 压缩后保存路径
     */
    if (!function_exists('image_size_add')) {
        function image_size_add($imgsrc, $imgdst)
        {
            list($width, $height, $type) = getimagesize($imgsrc);
            $new_width = ($width > 600 ? 600 : $width) * 0.9;
            $new_height = ($height > 600 ? 600 : $height) * 0.9;
            switch ($type) {
                case 1:
                    $giftype = check_gifcartoon($imgsrc);
                    if ($giftype) {
                        header('Content-Type:image/gif');
                        $image_wp = imagecreatetruecolor($new_width, $new_height);
                        $image = imagecreatefromgif($imgsrc);
                        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        imagejpeg($image_wp, $imgdst, 75);
                        imagedestroy($image_wp);
                    }
                    break;
                case 2:
                    header('Content-Type:image/jpeg');
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromjpeg($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    imagejpeg($image_wp, $imgdst, 75);
                    imagedestroy($image_wp);
                    break;
                case 3:
                    header('Content-Type:image/png');
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefrompng($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    imagejpeg($image_wp, $imgdst, 75);
                    imagedestroy($image_wp);
                    break;
            }
        }
    }
    /**
     * desription 判断是否gif动画
     * @param string $image_file图片路径
     * @return boolean t 是 f 否
     */
    if (!function_exists('thumbImage')) {
        function check_gifcartoon($image_file)
        {
            $fp = fopen($image_file, 'rb');
            $image_head = fread($fp, 1024);
            fclose($fp);
            return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
        }
    }

}
