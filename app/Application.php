<?php
namespace App;
use App\Lib\Config;
use App\Lib\Console;
use App\Lib\Database;
use App\Lib\Log;
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
    /**
     * @var View
     */
    public $view;
    /**
     * @var Config
     */
    public $config;
    public $log;
    /**
     * @var Database
     */
    public $db;

    public function __construct($config_path)
    {
        $this->setConfig($config_path);
        $this->setLog();
        $this->setDB();
    }

    protected function setConfig($config_path) {
        $this->config = new Config($this, $config_path);
    }

    protected function setDB() {
        $this->db = new Database($this);
    }

    protected function setLog() {
        $this->log = new Log($this);
    }

    public function bootHttp() {
        $route_path = require_once PROJECT_ROOT . DS . 'bootstrap' . DS . 'routes.php';
        $view_path = PROJECT_ROOT . DS . 'resource' . DS . 'views';
        $this->request = new Request($this);
        $this->response = new Response($this);
        $this->router = new Router($this, $route_path);
        $this->view = new View($this, $view_path);
    }

    public function bootConsole($arguments) {
        $this->console = new Console($this, $arguments);
    }

    public function handleRequest() {
        return $this->router->handle();
    }

    public function handleCommand() {
        return $this->console->handle();
    }


}