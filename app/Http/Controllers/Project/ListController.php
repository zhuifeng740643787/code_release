<?php
namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\Server;

class ListController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $projects = Project::all();
        $projects = array_map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'repository' => $item->repository,
                'status' => $item->status,
                'status_info' => $item->status == Server::ENABLE ? '启用' : '禁用',
                'created_at' => $item->created_at
            ];
        }, $projects);
        return $response->jsonSuccess([
            'rows' => $projects
        ]);
    }

}