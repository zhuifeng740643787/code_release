<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 上午11:48
 */

require PROJECT_ROOT . DS . 'bootstrap' . DS . 'autoload.php';
spl_autoload_register('autoload');

$app = new \App\Application();
$routes = require_once PROJECT_ROOT . DS . 'bootstrap' . DS . 'routes.php';
$app->bootHttp($routes);
$app->setView(PROJECT_ROOT . DS . 'resource' . DS . 'views');
$app->setConfig(PROJECT_ROOT . DS . 'config');
$app->setLog();
$app->handleRequest();

