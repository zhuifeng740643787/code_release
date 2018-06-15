<?php

if (!function_exists('env')) {

    function env($param, $default = null) {
        $env_file = __DIR__ . DS . ".env.php";
        if (!file_exists($env_file)) {
            throw new Exception('need .env.php file');
        }
        $env_config = include $env_file;

        if (isset($env_config[$param])) {
            return $env_config[$param];
        }

        return $default;
    }
}

if (!function_exists('app')) {

    function app() {
        global $app;
        return $app;
    }
}



