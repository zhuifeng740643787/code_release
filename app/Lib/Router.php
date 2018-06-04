<?php
namespace App\Lib;
use App\Application;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Router implements IKernel
{
    public $app;
    protected $routes;

    /**
     * Router constructor.
     * @param $app Application
     * @param $routes
     */
    public function __construct($app, $routes)
    {
        $this->app = $app;
        $this->routes = $routes;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $request_uri = $this->app->request->getServer('REQUEST_URI');

        $route = $this->_processRequestUri($request_uri);
        if(!isset($this->routes[$route])) {
            return $this->app->response->jsonError('404 not found');
        }

        // 设置请求数据
        $this->app->request->route = $route;
        $handle_ctl_action = explode('@', $this->routes[$route]);
        $controller = new $handle_ctl_action[0];

        return isset($handle_ctl_action[1]) ? $controller->{$handle_ctl_action[1]}($this->app->request, $this->app->response) : $controller->index($this->app->request, $this->app->response);
    }

    private function _processRequestUri($request_uri) {
        $request_uri = trim($request_uri);
        $request_uri_arr = explode('?', $request_uri);
        $route = $request_uri_arr[0] === '/' ? $request_uri_arr[0] : rtrim($request_uri_arr[0], '/');
        return $route;
    }

}