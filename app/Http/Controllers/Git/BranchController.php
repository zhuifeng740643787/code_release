<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午3:41
 */

namespace App\Http\Controllers\Git;


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
        $deploy_default_config['repository'] = $repositories[$project_name];
        $deploy_default_config['project_name'] = $project_name;
        $new_deploy_config = var_export($deploy_default_config, true);
        $deploy_config_file = CONFIG_ROOT . DS . 'deploy.php';
        // 写入配置文件
        file_put_contents($deploy_config_file, "<?php return $new_deploy_config;");
        $dep_cmd_path = $deploy_default_config['local_dep_path'];
        $dep_path = PROJECT_ROOT . DS . 'deploy';
        exec("cd $dep_path && $dep_cmd_path get_repository_branches local", $result);
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