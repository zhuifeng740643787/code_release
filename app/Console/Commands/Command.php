<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */

namespace App\Console\Commands;

use App\Application;

abstract class Command
{
    protected $app;
    public static $name = ''; // 名称
    public static $description = ''; // 描述
    protected $params = []; // 请求参数

    /**
     * Command constructor.
     * @param $app Application
     * @param array $params
     */
    public function __construct($app, $params = [])
    {
        $this->app = $app;
        if (is_array($params)) {
            $this->params = $params;
        }
    }

    public function getName()
    {
        return static::$name;
    }

    public function getDescription()
    {
        return static::$description;
    }

    abstract public function run();
}