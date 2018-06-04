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
     * @param $config app.hosts
     * @return mixed|void
     */
    public function get($config) {
        if (!$config) {
            return;
        }

        $config_arr = explode('.', $config);
        $config_file = $this->file_path . DS . $config_arr[0] . '.php';
        if (!file_exists($config_file)) {
            throw new \Exception('配置文件不存在:' . $config_file);
        }

        $obj = include_once $config_file;
        unset($config_arr[0]);
        foreach ($config_arr as $item) {
            $obj = $obj[$item];
        }

        return $obj;
    }




}