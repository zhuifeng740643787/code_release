<?php

namespace App\Http\Controllers\Server;

use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Server;
use App\Model\ServerGroup;
use App\Model\ServerGroupCombination;

class GroupController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $server_group_combinations = ServerGroupCombination::allEnables();
        $group_ids = Utils::collectFields($server_group_combinations, 'group_id');
        $server_ids = Utils::collectFields($server_group_combinations, 'server_id');
        $groups = ServerGroup::inIds($group_ids);
        $groups = Utils::collectSetFieldAsKey($groups);
        $servers = Server::inIds($server_ids);
        $servers = Utils::collectSetFieldAsKey($servers);
        $rows = [];
        foreach ($server_group_combinations as $item) {
            $group = $groups[$item->group_id];
            $server = $servers[$item->server_id];
            if (!isset($rows[$item->group_id])) {
                $rows[$item->group_id] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'items' => [],
                ];
            }
            $rows[$item->group_id]['items'][] = [
                'server_id' => $server->id,
                'server_name' => $server->name,
            ];
        }
        return $response->jsonSuccess([
            'rows' => array_values($rows)
        ]);
    }

}