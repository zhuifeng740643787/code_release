<?php
namespace App\Lib;
use App\Application;
use App\Console\Kernel;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Console implements IKernel
{
    public $app;
    protected $arguments; // 命令行参数

    /**
     * Request constructor.
     * @param $app Application
     * @param array $arguments
     */
    public function __construct($app, $arguments)
    {
        $this->app = $app;
        $this->arguments = $arguments;
    }


    public function handle() {
        unset($this->arguments[0]);
        $command = '';
        if (isset($this->arguments[1])) {
            $command = $this->arguments[1];
            unset($this->arguments[1]);
        }
        $kernel = new Kernel();
        return $kernel->handle($this->app, $command, array_values($this->arguments));
    }

}