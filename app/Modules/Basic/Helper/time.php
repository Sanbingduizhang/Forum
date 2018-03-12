<?php

if (!function_exists('sec_trans_to_hhmmss')) {
    /**
     * 将秒的数值转换成hh:mm:ss格式的时长字串
     *
     * @param int $sec
     * @return string
     */
    function sec_trans_to_hhmmss($sec = 0)
    {
        $hour = floor($sec/3600);
        $sec -= $hour * 3600;
        $hour = $hour == 0 ? '00' : ($hour < 10 ? '0'.$hour : $hour);

        $minute = floor($sec/60);
        $sec -= $minute * 60;
        $minute = $minute == 0 ? '00' : ($minute < 10 ? '0'.$minute : $minute);

        $second = $sec == 0 ? '00' : ($sec < 10 ? '0'.$sec : $sec);

        return "{$hour}:{$minute}:{$second}";
    }
}

if (!function_exists('hhmmss_trans_to_sec')) {
    /**
     * 将hh:mm:ss格式转换成秒的数值
     *
     * @param string $hhmmss
     * @return int
     */
    function hhmmss_trans_to_sec($hhmmss = '')
    {
        list($h, $m, $s) = explode(':', $hhmmss);
        return (int) $h * 3600 + $m * 60 + $s;
    }

}