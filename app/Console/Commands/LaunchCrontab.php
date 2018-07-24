<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */

namespace App\Console\Commands;

use App\Helper\Request;
use App\Helper\Utils;
use App\Model\Crontab;
use App\Model\CrontabProject;
use App\Model\CrontabServer;

class LaunchCrontab extends Command
{

    public static $name = 'launch_crontab';
    public static $description = '执行定时任务';

    private $_release_url = '';

    public function run()
    {

        $this->_release_url = rtrim(app()->config->get('app.web_host'), '/') . '/release';
        Utils::log('launch_crontab start');
        // 判断是否有要执行的计划任务
        $list = Crontab::allEnables();
        if (empty($list)) {
            Utils::log('无任务');
            return;
        }
        foreach ($list as $row) {
            $this->_process($row);
        }
        Utils::log('launch_crontab end');
    }

    // 处理
    private function _process($crontab)
    {
        Utils::log('launch_crontab ' . $crontab->title . ' start');
        $crontab_servers = CrontabServer::search('*', 'crontab_id=:CRONTAB_ID and status=:STATUS', [
            ':CRONTAB_ID' => $crontab->id,
            ':STATUS' => CrontabServer::ENABLE,
        ]);
        if (empty($crontab_servers)) {
            Utils::log('launch_crontab ' . $crontab->title . ' 无服务器');
            return;
        }
        $crontab_projects = CrontabProject::search('*', 'crontab_id=:CRONTAB_ID and status=:STATUS', [
            ':CRONTAB_ID' => $crontab->id,
            ':STATUS' => CrontabProject::ENABLE,
        ]);
        if (empty($crontab_projects)) {
            Utils::log('launch_crontab ' . $crontab->title . ' 无项目');
            return;
        }

        $server_ids = Utils::collectFields($crontab_servers, 'server_id');
        $project_arr = [];
        foreach ($crontab_projects as $project) {
            $project_arr[] = [
                'id' => $project->project_id,
                'branch_tag' => ($project->branch_type == CrontabProject::BRANCH_TYPE_BRANCH ? 'branch-' : 'tag-') . $project->branch_name,
                'replace_files' => [],
            ];
        }
        $response = Request::get($this->_release_url, [
            'server_ids' => $server_ids,
            'release_code_path' => $crontab->release_code_path,
            'remark' => $crontab->remark,
            'projects' => $project_arr
        ]);
        $response = json_decode($response);
        if ($response && $response->status == 'success') {
            Utils::log('launch_crontab ' . $crontab->title . ' 执行成功:' . ' 版本号=['.$response->result->version_num . ']');
        } else {
            Utils::log('launch_crontab ' . $crontab->title . ' 执行失败');
        }
        Utils::log('launch_crontab ' . $crontab->title . ' end');
    }

}