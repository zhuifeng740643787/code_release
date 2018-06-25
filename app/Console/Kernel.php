<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:28
 */

namespace App\Console;

use App\Console\Commands\InitProjects;
use App\Console\Commands\InitProjectsCode;
use App\Console\Commands\LaunchReleaseJob;
use App\Console\Commands\ProcessTask;
use App\Console\Commands\Test;
use App\Console\Commands\UpAndDeployCode;
use App\Helper\Utils;

class Kernel
{

    protected $commands = [
        InitProjectsCode::class,
        LaunchReleaseJob::class,
        ProcessTask::class,
    ];

    public function handle($app, $command_name, $params)
    {
        if (empty($command_name)) {
            return $this->help();
        }
        foreach ($this->commands as $command) {
            if ($command::$name == $command_name) {
                return (new $command($app, $params))->run();
            }
        }
        Utils::log('命令未找到...', false);
        return $this->help();
    }

    public function help()
    {
        print_r("=================================================================" . PHP_EOL);
        print_r("帮助文档" . PHP_EOL);
        foreach ($this->commands as $command) {
            print_r("-----------------------------------------------------------------" . PHP_EOL);
            printf("%' -30s %s " . PHP_EOL, $command::$name, $command::$description);
        }
        print_r("=================================================================" . PHP_EOL);
    }

}