<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */

namespace App\Console\Commands;

use App\Helper\Utils;

class LaunchReleaseJob extends Command
{

    public static $name = 'launch_release_job';
    public static $description = '启动监听代码上传脚本';

    public function run()
    {
        Utils::log('launch_release_job start');
        $this->launchCommand('process_task', 10);
        Utils::log('launch_release_job end');
    }


    // 启动命令
    protected function launchCommand($command, $sleep_seconds = 10)
    {
        // 判断事件是否已经启动
        if ($this->isProcessExists($command)) {
            Utils::log('launch_release_job job=' . $command . ' 已启动 ');
            return true;
        }
        $log_file = STORAGE_ROOT . DS . 'app' . DS . 'log' . DS . 'crontab.log';
        $cmd = PROJECT_ROOT . DS . "loop_command $command $sleep_seconds >> $log_file 2>&1 &";
        $exec_result = Utils::runExec($cmd);
        if (false === $exec_result) {
            Utils::log('launch_release_job job=' . $command . ' 启动失败');
            return false;
        }
        Utils::log('launch_release_job job=' . $command . ' 启动成功');
        return true;
    }

    // 判断进程是否已存在
    protected function isProcessExists($command)
    {
        $process_info = Utils::runExec("ps aux|grep loop_command| grep $command| grep -v 'grep'");
        if (false === $process_info || empty($process_info)) {
            return false;
        }
        return true;
    }


}