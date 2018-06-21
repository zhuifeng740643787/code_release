<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */

namespace App\Console\Commands;

use App\Helper\Task;
use App\Helper\Utils;
use App\Model\TaskGroup;

class ProcessTask extends Command
{

    public static $name = 'process_task';
    public static $description = '处理任务，上传且部署代码(服务器)';

    protected $task_path;
    protected $release_path;
    protected $deploy_release_project_name;

    public function run()
    {
        Utils::log('process_task start');
        // 获取未开始的任务组
        $task_group = TaskGroup::first('*', "status=:STATUS", [':STATUS' => TaskGroup::STATUS_CREATED]);
        if (!$task_group) {
            Utils::log("无任务");
            return;
        }




        // 执行组任务：
        // - 各个子项目代码复制->切换分支/标签->替换文件->写入日志
        // - 项目代码打包

        // 执行子任务：
        // - 上传至服务器
        // - 解压并部署
        // - 保留历史版本

    }

    // 获取未开始的任务组
    private function _getUnStartTaskGroup() {

    }

}