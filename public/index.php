<?php
//入口文件
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', __DIR__);//入口文件目录
define("PROJECT_ROOT", dirname(BASE_DIR));
define('DEPLOY_ROOT', PROJECT_ROOT . DS . 'deploy');
define("CONFIG_ROOT", PROJECT_ROOT .DS . 'config');
define('STORAGE_ROOT', PROJECT_ROOT . DS . 'storage');
define('TMP_ROOT', STORAGE_ROOT . DS . 'tmp');

ini_set('date.timezone', 'Asia/Shanghai');

require PROJECT_ROOT . DS . '_ide_helper.php';
require PROJECT_ROOT . DS . 'bootstrap' . DS . 'autoload.php';
spl_autoload_register('autoload');

$app = new \App\Application(CONFIG_ROOT);
$app->bootHttp();
$app->handleRequest();


