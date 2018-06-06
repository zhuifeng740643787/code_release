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
use App\Console\Commands\UpAndDeployCode;
use App\Helper\Utils;

class Kernel {

    protected $commands = [
        InitProjectsCode::class,
        LaunchReleaseJob::class,
        UpAndDeployCode::class,
    ];

    public function handle($app, $command_name, $params) {
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

    public function help() {
        Utils::log("===================================================", false);
        Utils::log("帮助文档", false);
        foreach ($this->commands as $command) {
            Utils::log("---------------------------------------------------", false);
            Utils::log($command::$name . "\t" . $command::$description, false);
        }
        Utils::log("===================================================", false);
    }

}