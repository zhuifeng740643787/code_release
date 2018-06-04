<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */
namespace App\Console\Commands;

use App\Helper\Utils;

class InitProjectsCode extends Command
{

    public static $name = 'init_projects_code';
    public static $description = '初始化项目代码';

    public function run()
    {
        Utils::log('------------- init_projects start --------------');
        $app_config = $this->app->config->get('app');
        $config_path = PROJECT_ROOT . DS . 'config';
        // 默认deploy配置
        $deploy_default_config_file = $config_path . DS . 'deploy_default.php';
        $deploy_default_config = include_once $deploy_default_config_file;
        $deploy_config_file = $config_path . DS . 'deploy.php';
        $repositories = $app_config['repositories'];
        $deploy_path = PROJECT_ROOT . DS . 'deploy';
        $dep_cmd_path = $deploy_default_config['local_dep_path'];
        foreach ($repositories as $project_name => $repository) {
            Utils::log("$repository start");
            // 重置配置文件
            $reset_config = $this->_makeDeployConfig($deploy_default_config, $deploy_config_file, $project_name, $repository, 'master');
            // 执行拉取任务
            if (!$reset_config) {
                Utils::log("$repository 重置失败");
                continue;
            }
            $result = exec("cd $deploy_path && $dep_cmd_path init_repository_code local");
            Utils::log("$repository $result end");
        }
        Utils::log('------------- init_projects end --------------');
    }


    // 重新设置deploy配置，并写入文件
    private function _makeDeployConfig($default_config, $config_file, $project_name, $repository, $branch_name) {
        $default_config['repository'] = $repository;
        $default_config['project_name'] = $project_name;
        $default_config['branch'] = $branch_name;
        $content = var_export($default_config, true);
        if (false !== file_put_contents($config_file, "<?php " . PHP_EOL . "return " . $content . ";")) {
            return true;
        }

        return false;
    }




}