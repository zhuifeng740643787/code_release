<?php

namespace App\Lib;
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class View
{

    public $app;
    public $path;
    static $instance;


    public function __construct($app, $path)
    {
        $this->app = $app;
        $this->path = $path;
        self::$instance = $this;
    }

    // 成功
    public function render($file)
    {
        $path = empty($this->path) ? self::$instance->path : $this->path;
        include $path . DS . $file . '.php';
        exit;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$instance, $name], $arguments);
    }

}