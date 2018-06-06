<?php
namespace App\Lib;
use App\Application;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Config
{
    public $app;
    protected $file_path; // 文件路径

    public static $included_files = []; // 已经包含的文件

    /**
     * Config constructor.
     * @param $app Application
     * @param $file_path String
     * @throws \Exception
     */
    public function __construct($app, $file_path)
    {
        $this->app = $app;
        if (!is_dir($file_path)) {
            throw new \Exception('配置文件路径不存在');
        }
        $this->file_path = $file_path;
    }

    /**
     * 获取配置
     * @param $config
     * @return mixed|void
     * @throws \Exception
     */
    public function get($config) {
        if (!$config) {
            return;
        }

        $config_arr = explode('.', $config);
        $config_file_name = $config_arr[0];
        if (isset(self::$included_files[$config_file_name])) {
            $obj = self::$included_files[$config_file_name];
        } else {

            $config_file = $this->file_path . DS . $config_file_name . '.php';
            if (!file_exists($config_file)) {
                throw new \Exception('配置文件不存在:' . $config_file);
            }

            $obj = include $config_file;

            self::$included_files[$config_file_name] = $obj;
        }
        unset($config_arr[0]);
        foreach ($config_arr as $item) {
            $obj = $obj[$item];
        }

        return $obj;
    }




}