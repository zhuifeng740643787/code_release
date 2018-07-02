<?php
namespace App\Http\Controllers\Other;

use App\Helper\Code;
use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\ProjectGroup;
use App\Model\ProjectGroupCombination;
use App\Model\ProjectStaticFile;
use App\Model\Server;
use App\Model\ServerGroup;
use App\Model\ServerGroupCombination;

class ConfigController extends Controller
{
    protected $params = [];
    public function index(Request $request, Response $response)
    {
        $this->params['is_test'] = $request->get('is_test', 0);
        return $response->jsonSuccess([
            'server_groups' => $this->_serverGroups(),
            'project_groups' => $this->_projectGroups()
        ]);
    }

    private function _serverGroups() {
        if (!$this->params['is_test']) {
            $groups = ServerGroup::allEnables();
        } else {
            $groups = ServerGroup::search('*', 'is_test=:IS_TEST and status=:STATUS', [':IS_TEST' => ServerGroup::YES, ':STATUS' => ServerGroup::ENABLE]);
        }
        $groups = Utils::collectSetFieldAsKey($groups, 'id');
        $group_ids = Utils::collectFields($groups, 'id');
        $server_groups = ServerGroupCombination::getServersByGroupIds($group_ids);
        $server_ids = Utils::collectFields($server_groups, 'server_id');
        $servers = Server::inIdsEnables($server_ids);
        $servers = Utils::collectSetFieldAsKey($servers, 'id');
        $ret = [];

        foreach ($server_groups as $item) {
            $group_id = $item->group_id;
            if (!isset($ret[$group_id])) {
                $ret[$group_id] = [
                    'id' => $group_id,
                    'name' => $groups[$group_id]->name,
                    'servers' => []
                ];
            }
            $server_id = $item->server_id;
            if (!isset($servers[$server_id])) {
                continue;
            }

            $ret[$group_id]['servers'][] = [
                'id' => $servers[$server_id]->id,
                'name' => $servers[$server_id]->name,
                'host' => $servers[$server_id]->host,
            ];
        }

        return array_values($ret);
    }

    private function _projectGroups() {
        if (!$this->params['is_test']) {
            $groups = ProjectGroup::allEnables();
        } else {
            $groups = ProjectGroup::search('*', 'is_test=:IS_TEST and status=:STATUS', [':IS_TEST' => ProjectGroup::YES, ':STATUS' => ProjectGroup::ENABLE]);
        }
        $groups = Utils::collectSetFieldAsKey($groups, 'id');
        $group_ids = Utils::collectFields($groups, 'id');
        $project_groups = ProjectGroupCombination::getProjectsByGroupIds($group_ids);
        $project_ids = Utils::collectFields($project_groups, 'project_id');
        $projects = Project::inIdsEnables($project_ids);
        $projects = Utils::collectSetFieldAsKey($projects, 'id');
        $project_static_files_arr = $this->_getProjectStaticFiles($project_ids);
        $ret = [];
        foreach ($project_groups as $item) {
            $group_id = $item->group_id;
            if (!isset($ret[$group_id])) {
                $ret[$group_id] = [
                    'id' => $group_id,
                    'name' => $groups[$group_id]->name,
                    'projects' => []
                ];
            }
            $project_id = $item->project_id;
            if (!isset($projects[$project_id])) {
                continue;
            }

            $ret[$group_id]['projects'][] = [
                'id' => $project_id,
                'name' => $projects[$project_id]->name,
                'repository' => $projects[$project_id]->repository,
                'static_files' => isset($project_static_files_arr[$project_id]) ? $project_static_files_arr[$project_id] : [],
                'branches' => $this->_getBranches($projects[$project_id]->name, $projects[$project_id]->repository),
                'tags' => $this->_getTags($projects[$project_id]->name, $projects[$project_id]->repository),
            ];
        }

        return array_values($ret);
    }

    private function _getProjectStaticFiles($project_ids) {
        // 获取静态替换文件
        $project_static_files = ProjectStaticFile::inIdsEnables($project_ids, 'project_id');
        $project_static_files_arr = [];
        foreach ($project_static_files as $item) {
            $project_id = $item->project_id;
            if (!isset($project_static_files_arr[$project_id])) {
                $project_static_files_arr[$project_id] = [];
            }
            $project_static_files_arr[$project_id][] = $item->file_path;
        }
        return $project_static_files_arr;
    }

    private function _getBranches($project_name, $project_repository) {
        $branches = Code::getRepositoryBranches($project_name, $project_repository);
        if (is_string($branches)) {
            return [];
        }
        return $branches;
    }

    private function _getTags($project_name, $project_repository) {
        $tags = Code::getRepositoryTags($project_name, $project_repository);
        if (is_string($tags)) {
            return [];
        }
        return $tags;
    }
}