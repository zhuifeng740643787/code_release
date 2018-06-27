<?php

namespace App\Http\Controllers\Release;

use App\Helper\Code;
use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Log;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\ProjectStaticFile;
use App\Model\Server;
use App\Model\Task;
use App\Model\TaskGroup;
use App\Model\TaskProject;
use App\Model\TaskProjectReplaceFile;
use App\Model\TaskServer;

class IndexController extends Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    private $_params = [];
    protected $version_num = '';//版本号
    protected $servers = []; // 服务器列表
    protected $projects = []; // 项目列表
    protected $project_static_files = []; // 项目静态文件

    const BRANCH_TAG_DELIMITER = '-'; // 分支/标签名称的分隔符

    public function index(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        // 生成版本号
        $this->version_num = date('YmdHis') . rand(1000, 9999);
        // 初始化参数
        $this->initParams();
        // 参数合法性检查
        if (true !== $check = $this->checkParams()) {
            return $response->jsonError($check);
        }
        // 创建任务
        return $this->_createTasks();
    }


    // 创建任务组及子任务
    private function _createTasks()
    {
        $task_file = TMP_ROOT . DS . 'task' . DS . $this->version_num;
        // 创建任务文件
        if (file_exists($task_file)) {
            return $this->response->jsonError('任务已存在');
        }

        $db = app()->db;
        $db->beginTransaction();
        try {
            // 写入任务组
            $task_group = TaskGroup::add([
                'version_num' => $this->version_num,
                'status' => TaskGroup::STATUS_CREATED,
                'release_code_path' => $this->_params['release_code_path'],
                'params' => json_encode($this->_params),
                'remark' => $this->_params['remark'],
            ]);
            $group_id = $task_group->id;
            // 创建任务服务器
            $this->_createServers($group_id);
            // 创建任务项目
            $this->_createProjects($group_id);
            $db->commit();
        } catch (\Exception $exception) {
            $db->rollback();
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
            return $this->response->jsonError('服务端异常');
        }

        return $this->response->jsonSuccess([
            'version_num' => $this->version_num,
            'params' => $this->_params
        ]);
    }

    // 创建任务服务器
    private function _createServers($group_id) {
        foreach ($this->servers as $server) {
            $task_server = TaskServer::add([
                'group_id' => $group_id,
                'server_id' => $server->id,
                'name' => $server->name,
                'host' => $server->host,
                'user' => $server->user,
                'password' => $server->password,
                'port' => $server->port,
            ]);
            // 创建子任务
            Task::add([
                'task_group_id' => $group_id,
                'task_server_id' => $task_server->id,
                'status' => Task::STATUS_CREATED,
            ]);
        }
    }

    // 创建任务项目
    private function _createProjects($group_id) {
        foreach ($this->_params['projects'] as $item) {
            $project_id = $item['id'];
            $project = $this->projects[$project_id];
            $branch_tag = $item['branch_tag'];
            $branch_tag_arr = explode(self::BRANCH_TAG_DELIMITER, $branch_tag);
            $task_project = TaskProject::add([
                'group_id' => $group_id,
                'project_id' => $project->id,
                'name' => $project->name,
                'repository' => $project->repository,
                'release_type' => array_search($branch_tag_arr[0], TaskProject::$release_type_map),
                'release_name' => $branch_tag_arr[1],
                'task_status' => TaskProject::TASK_STATUS_CREATED,
            ]);
            // 记录上传的文件，便于后续过滤静态文件，以上传的文件优先
            $upload_files = [];
            // 创建本地替换文件
            foreach ($item['replace_files'] as $file) {
                TaskProjectReplaceFile::add([
                    'task_project_id' => $task_project->id,
                    'type' => TaskProjectReplaceFile::TYPE_UPLOAD,
                    'local_file' => $file['local_file'],
                    'replace_file' => $file['replace_file'],
                ]);
                $upload_files[] = $this->_convertUploadFilePathToProjectFilePath($file['local_file'], $file['replace_file']);
            }
            // 创建静态文件
            if (!empty($this->project_static_files[$project_id])) {
                foreach ($this->project_static_files[$project_id] as $file_path) {
                    // 判断该文件夹是否已存在本地上传的文件中
                    if (in_array($file_path, $upload_files)) {
                        continue;
                    }
                    TaskProjectReplaceFile::add([
                        'task_project_id' => $task_project->id,
                        'type' => TaskProjectReplaceFile::TYPE_STATIC,
                        'local_file' => $file_path,
                    ]);
                }
            }
        }
    }

    // 将上传文件路径转为项目文件路径
    private function _convertUploadFilePathToProjectFilePath($upload_file_path, $project_file) {
        $upload_file_arr = explode(DS, $upload_file_path);
        $upload_file_name = $upload_file_arr[count($upload_file_arr) - 1];

        // 判断替换文件是文件还是目录
        $project_file = rtrim(trim($project_file), '/');
        $project_file_arr = explode('/', $project_file);
        $last_file = $project_file_arr[count($project_file_arr) - 1];
        // 是文件
        if (strpos($last_file, '.') > 0) {
            return $project_file;
        }

        // 是目录
        return $project_file . '/' . $upload_file_name;
    }

    // 接收参数
    protected function initParams()
    {
        $this->_params['server_ids'] = json_decode(trim($this->request->get('server_ids', '')), true);
        $this->_params['projects'] = json_decode(trim($this->request->get('projects', '')), true);
        $this->_params['release_code_path'] = trim($this->request->get('release_code_path', ''));
        $this->_params['remark'] = trim($this->request->get('remark', ''));
    }

    // 检查参数
    protected function checkParams()
    {
        if (empty($this->_params['release_code_path'])) {
            return '请填写项目发布目录';
        }
        // 检查服务器
        if (true !== $check_servers = $this->_checkServers()) {
            return $check_servers;
        }

        // 检查项目
        if (true !== $check_projects = $this->_checkProjects()) {
            return $check_projects;
        }
        return true;
    }

    // 检查服务器
    private function _checkServers()
    {
        if (empty($this->_params['server_ids'])) {
            return '请选择服务器';
        }
        $this->servers = Utils::collectSetFieldAsKey(Server::inIdsEnables($this->_params['server_ids']));
        if (count($this->servers) !== count($this->_params['server_ids'])) {
            return '有不可用的服务器';
        }
        foreach ($this->servers as $server) {
            if (!Code::isServerVaild($server->name, $server->host, $server->user)) {
                return "服务器{$server->name}不可用，请检查";
            }

            // todo 检查是否有写入版本目录的权限

        }
        return true;
    }

    // 检查项目
    private function _checkProjects()
    {
        if (empty($this->_params['projects'])) {
            return '请选择项目';
        }
        $project_ids = Utils::collectFields($this->_params['projects'], 'id');
        $this->projects = Utils::collectSetFieldAsKey(Project::inIdsEnables($project_ids));
        if (count($this->projects) !== count($project_ids)) {
            return '有不可用的项目';
        }
        $upload_file_path = app()->config->get('app.upload_file_path');
        foreach ($this->_params['projects'] as $row) {
            // 检查分支/标签是否存在
            $branch_tag = $row['branch_tag'];
            $project = $this->projects[$row['id']];
            $project_name = $project->name;
            if (empty($branch_tag)) {
                return "项目[{$project_name}]未选择分支或标签";
            }
            $branch_tag_arr = explode(self::BRANCH_TAG_DELIMITER, $branch_tag);
            if ($branch_tag_arr[0] == TaskProject::$release_type_map[TaskProject::RELEASE_TYPE_BRANCH]) {
                if (!in_array($branch_tag_arr[1], Code::getRepositoryBranches($project_name, $project->repository))) {
                    return "项目[$project_name]不存在分支'{$branch_tag_arr[1]}'";
                }
            } elseif ($branch_tag_arr[0] == TaskProject::$release_type_map[TaskProject::RELEASE_TYPE_TAG]) {
                if (!in_array($branch_tag_arr[1], Code::getRepositoryTags($project_name, $project->repository))) {
                    return "项目[$project_name]不存在标签'{$branch_tag_arr[1]}'";
                }
            } else {
                return "项目[{$project_name}]未选择分支或标签";
            }
            // 检查替换文件是否存在
            foreach ($row['replace_files'] as $item) {
                $local_file = $upload_file_path . DS . $item['local_file'];
                if (!file_exists($local_file)) {
                    return '要替换的文件:' . $item['local_file'] . '不存在';
                }
            }
        }

        // 项目静态文件
        $static_files = ProjectStaticFile::inIdsEnables($project_ids,'project_id');
        foreach ($static_files as $file) {
            if (!isset($this->project_static_files[$file->project_id])) {
                $this->project_static_files[$file->project_id] = [];
            }
            $this->project_static_files[$file->project_id][] = $file->file_path;
        }
        return true;
    }


}