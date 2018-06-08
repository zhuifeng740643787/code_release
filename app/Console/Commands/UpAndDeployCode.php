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

class UpAndDeployCode extends Command
{

    public static $name = 'up_and_deploy_code';
    public static $description = '上传且部署代码(服务器)';

    protected $task_path;
    protected $release_path;
    protected $deploy_release_project_name;

    public function run()
    {
        Utils::log('------------- up_and_deploy_code start --------------');
        $deploy_config = $this->app->config->get('deploy');
        $this->deploy_release_project_name = $deploy_config['release_project_name'];
        $this->task_path = $deploy_config['local_tmp_task_path'];
        $this->release_path = $deploy_config['local_tmp_release_path'];
        // 判断是否有任务
        $tasks = Task::getTasks($this->task_path);
        if (empty($tasks)) {
            Utils::log('------------- up_and_deploy_code 无任务 --------------');
            exit;
        }
        // 遍历处理有效的任务
        foreach ($tasks as $task_name) {
            $this->_processTask($task_name);
        }
        Utils::log('------------- up_and_deploy_code end --------------');
    }

    private function _processTask($task_name)
    {
        // 要发布的代码和服务器名
        list($release_code, $server_name) = Task::explodeTaskName($task_name);
        // 过滤掉无关的任务，保持deploy配置的一致性
        if ($release_code !== $this->deploy_release_project_name) {
            return false;
        }
        if (!file_exists($this->release_path . DS . $release_code)) {
            // 删除任务文件
            unlink($task_name);
            return false;
        }

        $task_file = $this->task_path . DS . $task_name;
        if (!file_exists($task_file)) {
            return false;
        }
        if (!is_readable($task_file)) {
            Utils::log('------------- up_and_deploy_code 任务文件' . $task_file . '不可读 --------------');
            return false;
        }
        // 先锁住文件
        $file_handle = fopen($task_file, 'r+');
        // 获取文件独占锁且不阻塞
        if (!flock($file_handle, LOCK_EX |LOCK_NB)) {
            fclose($file_handle);
            return false;
        }

        // 处理任务
        Utils::log('------------- up_and_deploy_code 处理任务中 ' . $task_name . '--------------');
        Utils::runDep('zip_and_up_code', 'local');
        Utils::runDep('server_release', $server_name);
        Utils::log('------------- up_and_deploy_code 处理任务完成 --------------');
        // 将文件清空
        ftruncate($file_handle, 0);
        flock($file_handle, LOCK_UN);
        // 删除任务文件
        unlink($task_file);
    }


}