<?php

namespace App\Lib;
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Log
{
    public $app;
    protected $path;
    protected $log_file;
    public static $instance;

    const LEVEL_DEBUG = 100;
    const LEVEL_INFO = 200;
    const LEVEL_WARNING = 300;
    const LEVEL_ERROR = 400;

    public static $level_map = [
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
    ];

    public function __construct($app)
    {
        $this->app = $app;
        $this->path = $app->config->get('app.log_path');
        $this->log_file = $this->path . DS . 'app.log';
        if (!file_exists($this->log_file)) {
            touch($this->log_file);
        }
        self::$instance = $this;
    }

    public static function debug($message)
    {
        self::$instance->_write($message, self::LEVEL_DEBUG);
    }

    public static function info($message)
    {
        self::$instance->_write($message, self::LEVEL_INFO);
    }

    public static function warning($message)
    {
        self::$instance->_write($message, self::LEVEL_WARNING);
    }

    public static function error($message)
    {
        self::$instance->_write($message, self::LEVEL_ERROR);
    }

    private function _write($message, $level = self::LEVEL_INFO) {
        $content = $this->_formatLog($message, $level);
        file_put_contents($this->log_file, $content , FILE_APPEND);
    }

    private function _formatLog($message, $level = self::LEVEL_INFO) {
        $content = is_object($message) || is_array($message) ? var_export($message, true) : $message;
        return "[" . date('Y-m-d H:i:s') . "] " . self::$level_map[$level] . ": " . $content . PHP_EOL;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$instance, $name], $arguments);
    }

}