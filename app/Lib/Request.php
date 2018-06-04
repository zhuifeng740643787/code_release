<?php
namespace App\Lib;
use App\Application;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Request
{
    public $app;
    protected $server;
    public $route;
    public $params = [];

    /**
     * Request constructor.
     * @param $app Application
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->server = $_SERVER;
        $this->params = $_REQUEST;
    }

    // 获取
    public function get($param_name, $default = null) {
        if (isset($this->params[$param_name])) {
            return $this->params[$param_name];
        }
        return $default;
    }

    public function all() {
        return $this->params;
    }

    public function getServer($param = null) {
        if (empty($param)) {
            return $this->server;
        }

        return isset($this->server[$param]) ? $this->server[$param] : null;
    }

}