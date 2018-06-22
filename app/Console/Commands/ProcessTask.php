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
    /**
     * @var TaskProject
     */
    protected $task_projects; // 子项目

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
        try {
            // 执行组任务：
            $this->_handleGroupTask();
            // 执行子任务：
            // - 上传至服务器
            // - 解压并部署
            // - 保留历史版本
        } catch (\Exception $exception) {
            // 任务报错
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
            $this->task_group->changeStatus(TaskGroup::STATUS_ERROR);
            Utils::log("处理任务组：ID={$this->task_group->id} 出错");
        }
        Utils::log("处理任务组：ID={$this->task_group->id} 结束");
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
        $this->task_name = $this->task_group->version_num;
        Utils::log("创建任务目录");
        $this->task_path = TMP_ROOT . DS . 'task' . DS . $this->task_name;
        // 创建任务目录
        Utils::runExec("mkdir -p {$this->task_path}");

        // - 各个子项目代码复制->切换分支/标签->替换文件->写入日志
        // 获取子项目
        Utils::log("获取子项目");
        $this->task_projects = TaskProject::select('*', 'group_id=:GROUP_ID and task_status=:TASK_STATUS', [':GROUP_ID' => $this->task_group->id, ':TASK_STATUS' => TaskProject::TASK_STATUS_CREATED]);
        // 任务项目的存放路径
        $task_code_path = $this->task_path . DS . 'code';
        Utils::runExec("mkdir -p $task_code_path");
        Utils::log("开始处理子项目");
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
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR);
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
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR);
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
            $project->changeStatus(TaskProject::TASK_STATUS_ERROR);
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
            $this->task_group->changeStatus(TaskGroup::STATUS_ERROR);
            Utils::log("项目代码打包出错");
            throw new \Exception('项目打包出错');
        } else {
            $this->task_group->changeStatus(TaskGroup::STATUS_PACKED);
            Utils::log("项目代码打包完成");
        }
    }

    // 修改任务状态


}