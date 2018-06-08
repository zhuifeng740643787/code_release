<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:46
 */
namespace App\Helper;

/**
 * 任务相关
 * Class Task
 * @package App\Helper
 */
class Task {

    // 任务名称的分割符
    private static $name_delimiter = '__host__';
    // 任务名称的前缀
    private static $name_prefix = 'task_';

    /**
     * 生成任务名称
     * @param $release_code_name 待发布代码名称
     * @param $server_name 服务器名称
     * @return string
     */
    public static function makeTaskName($release_code_name, $server_name) {
        return self::$name_prefix . $release_code_name . self::$name_delimiter . $server_name;
    }

    /**
     * 分解任务名
     * @param $task_name
     * @return array
     */
    public static function explodeTaskName($task_name) {
        return explode(self::$name_delimiter, mb_substr($task_name, mb_strlen(self::$name_prefix)));
    }

    /**
     * 判断是否为任务
     * @param $task_name
     * @return bool
     */
    public static function isTask($task_name) {
        if (empty(self::$name_prefix)) {
            return strpos($task_name, self::$name_delimiter) > 0;
        }

        return strpos($task_name, self::$name_prefix) === 0 && strpos($task_name, self::$name_delimiter) > 0;
    }


    /**
     * 判断是否存在任务
     * @param $task_path 任务文件所在目录
     * @return bool
     */
    public static function isExistsTask($task_path) {

        $dir_handle = opendir($task_path);
        while (false !== ($file = readdir($dir_handle))) {
            if (is_dir($file)) {
                continue;
            }

            var_dump($file, self::isTask($file));
            if (self::isTask($file)) {
                closedir($dir_handle);
                return true;
            }
        }
        closedir($dir_handle);
        return false;
    }


    /**
     * 获取所有任务
     * @param $task_path
     * @return array
     */
    public static function getTasks($task_path) {
        $tasks = [];
        $dir_handle = opendir($task_path);
        while (false !== ($file = readdir($dir_handle))) {
            if (is_dir($file)) {
                continue;
            }

            if (Task::isTask($file)) {
                $tasks[] = $file;
            }
        }
        closedir($dir_handle);
        return $tasks;
    }


}