<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午3:41
 */

namespace App\Http\Controllers\Git;

use App\Helper\Code;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;

class TagController extends Controller
{

    public function index(Request $request, Response $response)
    {

        $project_id = intval($request->get('project_id', 0));
        if (!$project_id) {
            return $response->jsonError('请先选择项目');
        }
        $project = Project::findEnable($project_id);
        if (empty($project)) {
            return $response->jsonError('项目未找到');
        }

        $tags = Code::getRepositoryTags($project->name, $project->repository);
        if (is_string($tags)) {
            return $response->jsonError($tags);
        }
        return $response->jsonSuccess([
            'rows' => $tags
        ]);
    }




}