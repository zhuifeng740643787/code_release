<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:24
 */


/**
 * 自动加载
 * @param $class
 */
function autoload($class) {
    if (strpos($class, 'App') !== 0) {
        return;
    }
    $class_path = str_replace('\\', '/', lcfirst($class));
    include PROJECT_ROOT . '/' . $class_path . '.php';
}