<?php
namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\Server;

class ConfigController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $servers = Server::allEnables();
        $projects = Project::allEnables();
        $servers = array_map(function($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'host' => $row->host,
            ];
        }, $servers);
        $projects = array_map(function($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'repository' => $row->repository,
            ];
        }, $projects);
        return $response->jsonSuccess([
            'servers' => $servers,
            'projects' => $projects
        ]);
    }
}