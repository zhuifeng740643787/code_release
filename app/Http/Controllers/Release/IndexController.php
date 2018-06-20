<?php

namespace App\Http\Controllers\Release;

use App\Helper\Code;
use App\Helper\Task;
use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class IndexController extends Controller
{
    protected $request;
    private $_params = [];
    protected $version_num = '';//版本号

    public function index(Request $request, Response $response)
    {
        $this->request = $request;
        // 生成版本号
        $this->version_num = date('YmdHis');
        // 初始化参数
        $this->_initParams($request);

        // 参数合法性检查
        if (true !== $check = $this->_checkParams()) {
            return $response->jsonError($check);
        }
        // 处理
        if (true !== $err_msg = $this->_handleRelease()) {
            return $response->jsonError($err_msg);
        }
        return $response->jsonSuccess([
            'release_project_name' => $this->_getReleaseProjectName(),
            'hosts' => $this->_params['hosts']
        ]);
    }


    // 发布
    private function _handleRelease()
    {
        // 重写deploy.php配置文件
        $this->_resetDeployConfig();


        // 1. 遍历检查服务器地址是否可用
        foreach ($this->_params['hosts'] as $host) {
            if (!$this->_checkHostValid($host)) {
                return '服务器[' . $host . ']不可用，请检查配置';
            }
        }

        // 2. 获取分支代码
        if (!Utils::runDep('get_branch_code', 'local')) {
            return '获取分支代码失败';
        }
        // 将代码复制到release目录
        $this->_cpCodeToRelease();

        // 3. 替换上传的文件并写入发版说明信息
        if (!$this->_replaceFiles()) {
            return '文件替换异常';
        }

        // 写入要发版的文件, 等待任务队列处理
        $this->_makeReleaseLogFile();

        return true;

    }

    // 写入要发版的文件
    private function _makeReleaseLogFile()
    {
        $deploy_config = $this->request->app->config->get('deploy');
        $release_path = $deploy_config['local_tmp_release_path'];
        $release_project_name = $this->_getReleaseProjectName();
        // 修改文件名称
        exec("cd $release_path && mv {$this->_params['project_name']} $release_project_name");

        // 生成发版文件 代码文件名+服务器名
        $task_path = $deploy_config['local_tmp_task_path'];
        foreach ($this->_params['hosts'] as $host) {
            $task_name = Task::makeTaskName($release_project_name, $host);
            $release_log_file = $task_path . DS . $task_name;
            // 创建任务
            file_put_contents($release_log_file, '');
        }
    }


    // 获取发布的项目名称，带版本号
    protected function _getReleaseProjectName()
    {
        $project_name = $this->_params['project_name'];
        return $project_name . '_version_' . $this->version_num;
    }

    // 将代码复制到release目录
    private function _cpCodeToRelease()
    {
        $deploy_config = $this->request->app->config->get('deploy');
        $code_path = $deploy_config['local_tmp_code_path'];
        $release_path = $deploy_config['local_tmp_release_path'];
        $project = $code_path . DS . $this->_params['project_name'];
        // 复制
        exec("rsync -a $project $release_path");
    }

    // 替换上传的文件并写入发版说明信息
    private function _replaceFiles()
    {
        $deploy_config = $this->request->app->config->get('deploy');
        $release_path = $deploy_config['local_tmp_release_path'];
        // 项目文件目录
        $project_path = $release_path . DS . $this->_params['project_name'];
        // 上传目录
        $upload_file_path = $this->request->app->config->get('app.upload_file_path');
        foreach ($this->_params['replace_files'] as $item) {
            $file = $upload_file_path . DS . $item['local_file'];
            $replace_file = $project_path . DS . $item['replace_file'];
            if (!Utils::replaceFile($file, $replace_file)) {
                return false;
            }
        }
        // 写入发版说明
        file_put_contents($project_path . DS . 'release.md', $this->_releaseRemarkFormat());
        return true;
    }

    // 版本发布说明
    private function _releaseRemarkFormat($project_name)
    {
        // 获取git的commit head
        $commit_head = Code::getGitHeadCommit($project_name);
        return '# version_' . $this->_params['branch'] . '_' . $this->version_num . PHP_EOL
            . '- ' . $commit_head[0] . PHP_EOL
            . '- ' . $this->_params['remark'] . PHP_EOL
            . '- ' . 'params: ' . var_export($this->_params, true) . PHP_EOL;
    }

    // 重写deploy.php配置文件
    private function _resetDeployConfig()
    {
        $app_config = $this->request->app->config->get('app');
        $hosts = $app_config['hosts'];
        $repositories = $app_config['repositories'];
        $deploy_config = $this->request->app->config->get('deploy_default');
        // 远程服务器
        $remote_servers = [];
        foreach ($this->_params['hosts'] as $server_name) {
            $remote_servers[$server_name] = [
                'host' => $hosts[$server_name]['host'],
                'user' => $hosts[$server_name]['user'],
            ];
        }
        $deploy_config['remote_servers'] = $remote_servers;
        $deploy_config['repository'] = $repositories[$this->_params['repository']]['address']; // 仓库地址
        $deploy_config['static_files'] = $repositories[$this->_params['repository']]['static_files']; // 静态文件
        $deploy_config['project_name'] = $this->_params['project_name']; // 项目名称, 也用作项目的目录名称
        $deploy_config['branch'] = $this->_params['branch']; // 分支名称
        $deploy_config['remote_code_release_path'] = $this->_params['project_path']; // 远程服务器的项目存放路径
        $deploy_config['release_project_name'] = $this->_getReleaseProjectName(); // 远程服务器的项目存放路径

        // 静态文件

        // 写入配置文件
        $deploy_config_file = CONFIG_ROOT . DS . 'deploy.php';
        Utils::writeConfigFile($deploy_config_file, $deploy_config);
    }

    // 检查服务器是否可用
    private function _checkHostValid($server_name, $host, $user)
    {
        return Code::isServerVaild($server_name, $host, $user);
    }

    // 接收参数
    private function _initParams()
    {
        $this->_params['branch'] = trim($this->request->get('branch', ''));
        $this->_params['hosts'] = json_decode(trim($this->request->get('hosts', '')));
        $this->_params['project_name'] = trim($this->request->get('project_name', ''));
        $this->_params['project_path'] = trim($this->request->get('project_path', ''));
        $this->_params['repository'] = trim($this->request->get('repository', ''));
        $this->_params['remark'] = trim($this->request->get('remark', ''));
        $this->_params['replace_files'] = json_decode(trim($this->request->get('replace_files', '')), true);
    }

    // 检查参数
    private function _checkParams()
    {
        if (empty($this->_params['branch'])) {
            return '请选择分支';
        }
        if (empty($this->_params['hosts'])) {
            return '请选择服务器';
        }
        if (empty($this->_params['project_name'])) {
            return '请填写项目名称';
        }
        if (empty($this->_params['project_path'])) {
            return '请填写项目目录';
        }
        if (empty($this->_params['repository'])) {
            return '请选择代码仓库';
        }
        // 替换文件
        if (!empty($this->_params['replace_files'])) {
            $upload_file_path = $this->request->app->config->get('app.upload_file_path');
            foreach ($this->_params['replace_files'] as $key => $item) {
                $local_file = $upload_file_path . DS . $item['local_file'];
                if (!file_exists($local_file)) {
                    return '要替换的第' . ($key + 1) . '个文件:' . $item['local_file'] . '不存在';
                }
            }
        }

        $app_config = $this->request->app->config->get('app');
        $hosts = $app_config['hosts'];
        $repositories = $app_config['repositories'];
        foreach ($this->_params['hosts'] as $host) {
            if (!isset($hosts[$host]) || empty($hosts[$host])) {
                return '服务器未进行配置';
            }
        }

        if (!isset($repositories[$this->_params['repository']]) || empty($repositories[$this->_params['repository']])) {
            return '代码仓库未进行配置';
        }
        return true;
    }



}