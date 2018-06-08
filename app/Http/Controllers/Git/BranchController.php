<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午3:41
 */

namespace App\Http\Controllers\Git;


use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class BranchController extends Controller
{

    public function index(Request $request, Response $response) {
        $project_name = $request->get('project_name', '');
        if (!$project_name) {
            return $response->jsonError('项目名称不能为空');
        }
        $repositories = $request->app->config->get('app.repositories');
        if (!isset($repositories[$project_name])) {
            return $response->jsonError('项目未设置');
        }

        $deploy_default_config = $request->app->config->get('deploy_default');
        $deploy_default_config['repository'] = $repositories[$project_name]['address'];
        $deploy_default_config['project_name'] = $project_name;

        // 写入配置文件
        $deploy_config_file = CONFIG_ROOT . DS . 'deploy.php';
        Utils::writeConfigFile($deploy_config_file, $deploy_default_config);
        // 执行dep任务
        $result = Utils::runDep('get_repository_branches', 'local');
        $branch_arr = [];
        foreach ($result as $row) {
            if (strpos($row, 'remotes/origin') === false) {
                continue;
            }
            if (strpos($row, 'remotes/origin/HEAD') !== false) {
                continue;
            }
            $branch_arr[] = trim(str_replace("✔ Ok", '', $row));
        }
        return $response->jsonSuccess([
            'rows' => str_replace('remotes/origin/', '', $branch_arr)
        ]);
    }

}