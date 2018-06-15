<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */
namespace App\Console\Commands;

use App\Helper\Utils;
use App\Model\Project;

class InitProjectsCode extends Command
{

    public static $name = 'init_projects_code';
    public static $description = '初始化项目代码';

    public function run()
    {
        Utils::log('------------- init_projects start --------------');
        $projects = Project::allEnables();
        // 默认deploy配置
        foreach ($projects as $project) {
            $project_name = $project->name;
            $repository = $project->repository;
            Utils::log("project $project_name start");
            $this->_getCode($project_name, $repository);
            Utils::log("project $project_name end");
        }
        Utils::log('------------- init_projects end --------------');
    }

    // 初始化仓库代码,有代码的话跳过，拉取master分支
    private function _getCode($project_name, $repository) {
        $deploy_config = app()->config->get('deploy');
        $git = $deploy_config['local_git_bin'];
        $code_path = realpath($deploy_config['local_tmp_code_path']) . DIRECTORY_SEPARATOR . $project_name;
        if (!file_exists($code_path)) {
            if (false === Utils::runExec("mkdir -p $code_path ")) {
                Utils::log('------------- init_projects ' . $project_name . ' error: 执行有误，无法创建文件--------------');
                return;
            }
        }
        // 判断是否有代码, 没有则拉取master分支的代码
        if (!file_exists($code_path . DIRECTORY_SEPARATOR . '.git')) {
            Utils::log('------------- init_projects ' . $project_name . ' info: 代码拉取开始 --------------');
            $ret = Utils::runExec("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone $repository $code_path");
            if (false === $ret) {
                Utils::log('------------- init_projects ' . $project_name . ' error: 代码拉取失败 --------------');
                return;
            } else {
                Utils::log('------------- init_projects ' . $project_name . ' success : 代码拉取成功 --------------');
            }
        } else {
            Utils::log('------------- init_projects ' . $project_name . ' info: 代码已存在 --------------');
        }
    }


}