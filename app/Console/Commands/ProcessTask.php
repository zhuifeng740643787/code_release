<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */

namespace App\Console\Commands;

use App\Helper\Code;
use App\Helper\Utils;
use App\Lib\Log;
use App\Model\Task;
use App\Model\TaskGroup;
use App\Model\TaskProject;
use App\Model\TaskProjectReplaceFile;
use App\Model\TaskServer;

class ProcessTask extends Command
{

    public static $name = 'process_task';
    public static $description = '处理任务，上传且部署代码(服务器)';

    protected $task_name; // 任务名称=版本号
    protected $task_path; // 任务所在目录
    /**
     * @var TaskGroup
     */
    protected $task_group; // 组任务
    protected $task_projects; // 子项目
    protected $task_servers;  // 服务器
    protected $deploy_path;   // deploy配置相关文件目录

    public function run()
    {
        Utils::log('process_task start');
        // 获取未开始的任务组
        $this->task_group = $this->_getUnStartTaskGroup();
        if (!$this->task_group) {
            Utils::log("无任务");
            return;
        }

        Utils::log("处理任务组：ID={$this->task_group->id} 开始");
        // 将组任务状态置为开始，防止其他进程执行该组任务
        $this->task_group->changeStatus(TaskGroup::STATUS_STARTED);
        $this->task_name = $this->task_group->version_num;
        $this->task_path = TMP_ROOT . DS . 'task' . DS . $this->task_name;
        // 获取子项目
        Utils::log("获取子项目");
        $this->task_projects = TaskProject::select('*', 'group_id=:GROUP_ID and task_status=:TASK_STATUS', [':GROUP_ID' => $this->task_group->id, ':TASK_STATUS' => TaskProject::TASK_STATUS_CREATED]);

        try {
            // 执行组任务：
            $this->_handleGroupTask();
            // 执行子任务：
            $this->_handleSubTasks();
            // 完成任务
        } catch (\Exception $exception) {
            // 任务报错
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
            $this->task_group->changeStatus(TaskGroup::STATUS_ERROR, mb_substr($exception->getMessage(), 0, 200));
            Utils::log("处理任务组：ID={$this->task_group->id} 出错");
        }
        $this->task_group->changeStatus(TaskGroup::STATUS_FINISHED);
        Utils::log("处理任务组：ID={$this->task_group->id} 结束");
        // 删除任务目录
        $this->_removeLocalTaskFiles();
        Utils::log('process_task end');
    }

    // 获取未开始的任务组
    private function _getUnStartTaskGroup()
    {
        return TaskGroup::first('*', "status=:STATUS", [':STATUS' => TaskGroup::STATUS_CREATED]);
    }

    // 执行组任务
    private function _handleGroupTask()
    {
        Utils::log("创建任务目录");
        // 创建任务目录
        Utils::runExec("mkdir -p {$this->task_path}");

        // - 各个子项目代码复制->切换分支/标签->替换文件->写入日志
        // 任务项目的存放路径
        $task_code_path = $this->task_path . DS . 'code';
        Utils::runExec("mkdir -p $task_code_path");
        Utils::log("开始处理子项目");
        if (empty($this->task_projects)) {
            Utils::log("无任务项目");
            $this->task_group->changeStatus(TaskGroup::STATUS_ERROR, '无任务项目');
            return;
        }
        foreach ($this->task_projects as $project) {
            // 项目代码复制->切换分支/标签->替换文件->写入release日志
            Utils::log("处理项目[{$project->name}]开始");
            // 复制代码
            $this->_copeProjectCode($project, $task_code_path);

            // 切换分支
            $this->_switchBranch($project, $task_code_path);

            // 替换文件
            $this->_replaceFiles($project, $task_code_path);

            // 写入发布日志
            $this->_writeReleaseLog($project, $task_code_path);

            // 处理项目完成
            Utils::log("处理项目[{$project->name}]完成");
        }
        // 代码复制完成
        $this->task_group->changeStatus(TaskGroup::STATUS_COPIED);
        // - 项目代码打包
        $zip_path = $this->task_path . DS . 'zip';
        $this->_zipCodes($task_code_path, $zip_path);
    }


    // 复制代码
    private function _copeProjectCode($project, $task_code_path)
    {
        Utils::log("复制代码开始");
        $exec_result = Code::copyProjectCode($project->name, $task_code_path);
        // 处理结果
        if (false === $exec_result) {
            // 失败
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR, '复制代码失败');
            throw new \Exception('复制代码失败');
        }
        $project->changeStatus(TaskProject::TASK_STATUS_COPIED);
        Utils::log("复制代码完成");
    }

    // 切换分支
    private function _switchBranch($project, $task_code_path)
    {
        Utils::log("切换分支到'{$project->release_name}'开始");
        $project_code_path = $task_code_path . DS . $project->name;
        if ($project->release_type == TaskProject::RELEASE_TYPE_BRANCH) {
            $exec_result = Code::getBranchCode($project_code_path, $project->release_name);
        } else {
            $exec_result = Code::getTagCode($project_code_path, $project->release_name);
        }

        if (false === $exec_result) {
            // 失败
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR, '切换分支失败');
            throw new \Exception('切换分支失败');
        }
        $project->changeStatus(TaskProject::TASK_STATUS_SWITCHED);
        Utils::log("切换分支到'{$project->release_name}'完成");
    }

    // 替换本地文件
    private function _replaceFiles($project, $task_code_path)
    {
        $project_code_path = $task_code_path . DS . $project->name;
        Utils::log("替换上传文件开始");
        $replace_files = TaskProjectReplaceFile::getFiles($project->id, TaskProjectReplaceFile::TYPE_UPLOAD);
        $exec_result = Code::replaceLocalFiles($project_code_path, $replace_files);
        // 执行失败
        if (false === $exec_result) {
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR, '替换本地文件失败');
            throw new \Exception('替换本地文件失败');
        }
        $project->changeStatus(TaskProject::TASK_STATUS_REPLACED);
        Utils::log("替换上传文件完成");
    }

    // 写入发布日志
    private function _writeReleaseLog($project, $task_code_path)
    {
        $project_code_path = $task_code_path . DS . $project->name;
        Utils::log("写入发布日志开始");
        $exec_result = Code::writeReleaseReadme($project_code_path, $this->task_group->version_num, $project->release_name, $project->release_type, $this->task_group->remark);
        // 执行失败
        if (false === $exec_result) {
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR);
            throw new \Exception('写入发布日志失败');
        }
        $project->changeStatus(TaskProject::TASK_STATUS_RELEASE_LOG);
        Utils::log("写入发布日志完成");
    }

    // 压缩项目文件
    private function _zipCodes($task_code_path, $zip_path)
    {
        if (!file_exists($zip_path)) {
            Utils::runExec("mkdir -p $zip_path");
        }
        Utils::log("项目代码打包,打包目录：'{$zip_path}'");
        $exec_result = Code::zipCode($task_code_path, $zip_path, $this->task_name . '.zip');
        if (false === $exec_result) {
            $this->task_group->changeStatus(TaskGroup::STATUS_ERROR, '项目打包出错');
            Utils::log("项目代码打包出错");
            throw new \Exception('项目打包出错');
        } else {
            $this->task_group->changeStatus(TaskGroup::STATUS_PACKED);
            Utils::log("项目代码打包完成");
        }
    }

    // 执行子任务
    private function _handleSubTasks()
    {
        Utils::log("处理子任务 开始");
        $this->task_servers = TaskServer::select('*', 'group_id=:GROUP_ID', [':GROUP_ID' => $this->task_group->id]);
        $this->task_servers = Utils::collectSetFieldAsKey($this->task_servers, 'id');

        Utils::log("写入deploy配置文件开始");
        // 写入dep配置文件
        $this->_createDeployConfig();
        Utils::log("写入deploy配置文件结束");

        // 子任务列表
        $tasks = Task::select('*', 'task_group_id=:TASK_GROUP_ID and status=:STATUS',
            [':TASK_GROUP_ID' => $this->task_group->id, ':STATUS' => Task::STATUS_CREATED]);
        if (empty($tasks)) {
            Utils::log("无子任务");
        }
        foreach ($tasks as $task) {
            // - 上传至服务器
            $task_server = $this->task_servers[$task->task_server_id];
            $this->_upZipCodeToServer($task, $task_server);

            // - 解压并部署
            $this->_unzipAndDeployOnServer($task, $task_server);
            // - 保留历史版本
            $this->_remainHistoryCodeOnServer($task, $task_server);
        }

        Utils::log("处理子任务 结束");
    }

    // 解压并部署
    private function _unzipAndDeployOnServer($task, $task_server) {
        Utils::log("解压并部署，服务器[$task_server->name]($task_server->host) 开始");
        $exec_result = Utils::runDep($this->deploy_path, 'unzip_and_deploy_code', $task_server->name);
        // 失败
        if (false === $exec_result) {
            $task->changeStatus(Task::STATUS_ERROR, "部署失败");
            throw new \Exception("服务器[$task_server->name]($task_server->host) 部署失败");
        }
        $task->changeStatus(Task::STATUS_DEPLOYED);
        Utils::log("解压并部署，服务器[$task_server->name]($task_server->host) 完成");
    }

    // 保留历史版本
    private function _remainHistoryCodeOnServer($task, $task_server) {
        Utils::log("保留历史版本，服务器[$task_server->name]($task_server->host) 开始");
        $exec_result = Utils::runDep($this->deploy_path, 'remain_history_version', $task_server->name);
        // 失败
        if (false === $exec_result) {
            $task->changeStatus(Task::STATUS_ERROR, "保留历史版本失败");
            throw new \Exception("服务器[$task_server->name]($task_server->host) 保留历史版本失败");
        }
        $task->changeStatus(Task::STATUS_FINISHED);
        Utils::log("保留历史版本，服务器[$task_server->name]($task_server->host) 完成");
    }
    // 生成deploy配置相关文件，用于后续deploy操作
    private function _createDeployConfig()
    {
        $this->deploy_path = $this->task_path . DS . 'deploy';
        // 参照文件
        $origin_deploy_path = DEPLOY_ROOT . DS . 'release';

        // 创建目录并复制文件
        $exec_result = Utils::runExec("mkdir -p $this->deploy_path && cp -R {$origin_deploy_path}\/* {$this->deploy_path}");
        if (false === $exec_result) {
            throw new \Exception("生成deploy配置出错");
        }

        $deploy_config = app()->config->get('deploy');
        $config_arr = [
            'version_num' => $this->task_group->version_num,
            'remote_unzip_bin' => $deploy_config['remote_unzip_bin'],
            'remote_servers' => $this->_formatRemoteServersForDeployConfig($this->task_servers),
            'remote_code_release_path' => $this->task_group->release_code_path,
            'identity_file_path' => $deploy_config['identity_file_path'],
            'remain_history_version_num' => $deploy_config['remain_history_version_num'],
            'projects' => $this->_formatProjectsForDeployConfig($this->task_projects)
        ];
        // 写入配置
        Utils::writeConfigFile($this->deploy_path . DS . 'config.php', $config_arr);
    }

    // 格式化服务器列表，供deploy config使用
    private function _formatRemoteServersForDeployConfig($remote_servers)
    {
        $server_arr = [];
        foreach ($remote_servers as $server) {
            $server_arr[] = [
                'name' => $server->name,
                'host' => $server->host,
                'user' => $server->user,
                'port' => $server->port,
                'password' => $server->password,
            ];
        }
        return $server_arr;
    }

    // 格式化项目列表，供deploy config使用
    private function _formatProjectsForDeployConfig($projects){
        $project_arr = [];
        foreach ($projects as $project) {
            $static_files = TaskProjectReplaceFile::getFiles($project->id, TaskProjectReplaceFile::TYPE_STATIC, false);
            $project_arr[] = [
                'name' => $project->name,
                'static_files' => array_map(function($item) {
                    return $item->local_file;
                }, $static_files)
            ];
        }
        return $project_arr;
    }

    // 上传至服务器
    private function _upZipCodeToServer($task, $task_server)
    {
        $zip_file = $this->task_path . DS . 'zip' . DS . $this->task_name . '.zip';
        Utils::log("上传至服务器[$task_server->name]($task_server->host) 开始");
        $exec_result = Code::upZipCode($this->task_group->release_code_path, $zip_file, $task_server->host, $task_server->user, $task_server->port);
        // 失败
        if (false === $exec_result) {
            $task->changeStatus(Task::STATUS_ERROR, "上传文件至服务器失败");
            Utils::log("上传至服务器[$task_server->name]($task_server->host) 失败");
        }
        $task->changeStatus(Task::STATUS_UPLOADED);
        Utils::log("上传至服务器[$task_server->name]($task_server->host) 完成");
    }


    // 删除当前任务目录
    private function _removeLocalTaskFiles()
    {
        Utils::log("删除任务目录 开始");
        $local_task_file = $this->task_path;
        $exec_result = Utils::runExec("rm -rf $local_task_file");
        if (false === $exec_result) {
            Utils::log("删除任务目录失败");
            return;
        }
        Utils::log("删除任务目录 完成");
    }

}