<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:46
 */

namespace App\Helper;

use App\Lib\Log;

class Utils
{

    /**
     * 打印日志
     * @param $message 日志内容
     * @param bool $show_time 是否显示时间
     */
    public static function log($message, $show_time = true)
    {
        if ($show_time) {
            print_r(date('Y-m-d H:i:s'));
            print_r("\t");
        }
        print_r($message);
        print_r("\t");
        echo PHP_EOL;
    }


    /**
     * 写入配置文件内容
     * @param $file_name
     * @param $content
     * @return bool
     */
    public static function writeConfigFile($file_name, $content)
    {
        $content = var_export($content, true);
        // 写入配置文件
        if (false !== file_put_contents($file_name, "<?php " . PHP_EOL . "return " . $content . ";")) {
            return true;
        }

        return false;
    }


    /**
     * 执行dep任务
     * @param $deploy_path 执行dep任务的目录
     * @param $task 任务名称
     * @param $server 执行的服务器
     * @return mixed
     */
    public static function runDep($deploy_path, $task, $server)
    {
        $deploy_config = app()->config->get('deploy');
        $dep_bin = $deploy_config['local_dep_bin'];
        return self::runExec("cd $deploy_path && $dep_bin $task $server");
    }

    /**
     * 文件替换
     * @param $file
     * @param $replace_file
     * @return bool
     */
    public static function replaceFile($file, $replace_file)
    {
        if (!file_exists($file) || !is_file($file)) {
            return false;
        }
        try {
            // 判断替换文件是否存在
            if (!file_exists($replace_file)) {
                // 判断替换文件是目录还是文件
                $file_explode = explode(DS, $replace_file);
                // 是文件
                if (strpos($file_explode[count($file_explode) - 1], '.') !== false) {
                    $dir = rtrim($replace_file, DS . $file_explode[count($file_explode) - 1]);
                } else {
                    $dir = $replace_file;
                }
                // 创建目录 并 将文件复制到替换目录
                exec("mkdir -p $dir && cp $file $dir");
            } else {
                // 将文件复制到替换目录
                exec("cp $file $replace_file");
            }
        } catch (\Exception $exception) {
            Log::error($exception->getTraceAsString());
            return false;
        }

        return true;
    }


    /**
     * 执行系统命令
     * @param $command
     * @return bool
     */
    public static function runExec($command)
    {
        exec($command, $output, $return_code);

        if ($return_code !== 0) {
            return false;
        }
        return $output;
    }



}