<?php
namespace App\Http\Controllers\Project;

use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\ProjectGroup;
use App\Model\ProjectGroupCombination;

class GroupController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $project_group_combinations = ProjectGroupCombination::allEnables();
        $group_ids = Utils::collectFields($project_group_combinations, 'group_id');
        $project_ids = Utils::collectFields($project_group_combinations, 'project_id');
        $groups = ProjectGroup::inIds($group_ids);
        $groups = Utils::collectSetFieldAsKey($groups);
        $projects = Project::inIds($project_ids);
        $projects = Utils::collectSetFieldAsKey($projects);
        $rows = [];
        foreach ($project_group_combinations as $item) {
            $group = $groups[$item->group_id];
            $project = $projects[$item->project_id];
            if (!isset($rows[$item->group_id])) {
                $rows[$item->group_id] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'items' => []
                ];
            }
            $rows[$item->group_id]['items'][] = [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ];
        }
        return $response->jsonSuccess([
            'rows' => array_values($rows)
        ]);
    }

}