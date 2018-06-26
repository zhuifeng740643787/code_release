<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Project;
use App\Model\ProjectStaticFile;
use App\Model\Server;

class ListController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $projects = Project::all();
        $static_files = ProjectStaticFile::allEnables();
        $project_file_arr = [];
        foreach ($static_files as $file) {
            if (!isset($project_file_arr[$file->project_id])) {
                $project_file_arr[$file->project_id] = [];
            }
            $project_file_arr[$file->project_id][] = $file->file_path;
        }
        $projects = array_map(function ($item) use ($project_file_arr) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'repository' => $item->repository,
                'status' => $item->status,
                'status_info' => $item->status == Server::ENABLE ? '启用' : '禁用',
                'static_files' => isset($project_file_arr[$item->id]) ? $project_file_arr[$item->id] : [],
                'created_at' => $item->created_at
            ];
        }, $projects);
        return $response->jsonSuccess([
            'rows' => $projects
        ]);
    }

}