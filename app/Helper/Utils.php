<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:46
 */
namespace App\Helper;

class Utils {

    /**
     * 打印日志
     * @param $message 日志内容
     * @param bool $show_time 是否显示时间
     */
    public static function log($message, $show_time = true) {
        if ($show_time) {
            print_r(date('Y-m-d H:i:s'));
            print_r("\t");
        }
        print_r($message);
        print_r("\t");
        echo PHP_EOL;
    }


}