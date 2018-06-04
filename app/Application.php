<?php
namespace App;
use App\Lib\Config;
use App\Lib\Console;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Router;
use App\Lib\View;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:46
 */

class Application {

    public $request;
    public $response;
    public $router;
    public $console;
    public $view;
    public $config;

    public function bootHttp($route_path) {
        $this->request = new Request($this);
        $this->response = new Response($this);
        $this->router = new Router($this, $route_path);
    }

    public function bootConsole($arguments) {
        $this->console = new Console($this, $arguments);
    }

    public function setView($view_path) {
        $this->view = new View($this, $view_path);
    }

    public function setConfig($config_path) {
        $this->config = new Config($this, $config_path);
    }

    public function handleRequest() {
        return $this->router->handle();
    }

    public function handleCommand() {
        return $this->console->handle();
    }


}