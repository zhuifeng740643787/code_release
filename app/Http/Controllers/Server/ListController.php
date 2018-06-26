<?php
namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Server;

class ListController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $servers = Server::all();
        $servers = array_map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'host' => $item->host,
                'user' => $item->user,
                'port' => $item->port,
                'status' => $item->status,
                'status_info' => $item->status == Server::ENABLE ? '启用' : '禁用',
                'created_at' => $item->created_at
            ];
        }, $servers);
        return $response->jsonSuccess([
            'rows' => $servers
        ]);
    }

}