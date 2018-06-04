<?php
//入口文件

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', __DIR__);//入口文件目录
define("PROJECT_ROOT", dirname(BASE_DIR));
define("CONFIG_ROOT", PROJECT_ROOT .DS . 'config');

require_once PROJECT_ROOT . DS . 'bootstrap' . DS . 'app.php';

