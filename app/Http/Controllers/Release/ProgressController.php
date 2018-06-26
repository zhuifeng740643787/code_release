<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/25
 * Time: 下午2:09
 */

namespace App\Http\Controllers\Release;

use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Model\Task;
use App\Model\TaskGroup;
use App\Model\TaskServer;

/**
 * 任务进度
 * Class ProgressController
 * @package App\Http\Controllers
 */
class ProgressController extends Controller
{

    protected $version_num; // 版本号
    protected $task_group; // 任务组

    // 0=进行中 1=完成 -1=出错
    const PROGRESS_ERROR = -1;
    const PROGRESS_ING = 0;
    const PROGRESS_OK = 1;


    public function index(Request $request, Response $response)
    {
        $this->version_num = $request->get('version_num', '');
        if (empty($this->version_num)) {
            return $response->jsonError("版本号不能为空");
        }

        $this->task_group = TaskGroup::searchOne('*', 'version_num=:VERSION_NUM', [':VERSION_NUM' => $this->version_num]);
        if (empty($this->task_group)) {
            return $response->jsonError("未找到组任务");
        }

        // 处理组任务进度
        return $response->jsonSuccess([
            'group' => $this->_groupProgress(),
            'sub' => $this->_subProgress(),
        ]);

    }

    private function _groupProgress()
    {
        $status_steps = TaskGroup::$status_steps;
        $ret = [
            'progress' => self::PROGRESS_ING, // 进度
            'status' => $this->task_group->status, // 当前状态
            'currentStep' => 0, // 当前步骤
            'error' => '', // 错误信息
            'steps' => [] // 步骤

        ];
        if ($ret['status'] === TaskGroup::STATUS_FINISHED) {
            $ret['progress'] = self::PROGRESS_OK;
        } elseif ($ret['status'] === TaskGroup::STATUS_ERROR) {
            $ret['progress'] = self::PROGRESS_ERROR;
            $ret['error'] = $this->task_group->status_info ? $this->task_group->status_info : '任务报错';
        }

        foreach ($status_steps as $key => $status) {
            if ($ret['status'] === TaskGroup::STATUS_ERROR) {
                if ($status == $ret['prev_status']) {
                    $ret['currentStep'] = $key + 1;
                }
            } else {
                if ($status == $ret['status']) {
                    $ret['currentStep'] = $key + 1;
                }
            }

            $ret['steps'][] = [
                'status' => $status,
                'name' => TaskGroup::$status_map[$status]
            ];
        }
        return $ret;
    }

    // 子任务进度
    private function _subProgress()
    {
        $tasks = Task::search('*', 'task_group_id=:TASK_GROUP_ID', [':TASK_GROUP_ID' => $this->task_group->id]);
        $task_server_ids = Utils::collectFields($tasks, 'task_server_id');
        $task_servers = TaskServer::inIds($task_server_ids, 'id');
        $task_servers = Utils::collectSetFieldAsKey($task_servers, 'id');
        $subs = [
            'progress' => self::PROGRESS_OK,
            'steps' => [],
            'tasks' => [],
        ];
        $status_steps = Task::$status_steps;
        foreach ($status_steps as $status) {
            $subs['steps'][] = [
                'status' => $status,
                'name' => Task::$status_map[$status],
            ];
        }
        $sub_has_error = false;
        $sub_has_ing = false;
        foreach ($tasks as $task) {
            $task_server = $task_servers[$task->task_server_id];
            if ($task->status == Task::STATUS_ERROR) {
                $index = array_search($task->prev_status, $status_steps);
            } else {
                $index = array_search($task->status, $status_steps);
            }
            $progress = $task->status === Task::STATUS_ERROR ? self::PROGRESS_ERROR : ($task->status === Task::STATUS_FINISHED ? self::PROGRESS_OK : self::PROGRESS_ING);
            if ($progress == self::PROGRESS_ING) {
                $sub_has_ing = true;
            }
            if ($progress == self::PROGRESS_ERROR) {
                $sub_has_error = true;
            }
            $subs['tasks'][] = [
                'server_name' => $task_server->name,
                'server_host' => $task_server->host,
                'progress' => $progress,
                'status' => $task->status,
                'currentStep' => $index === false ? 0 : $index + 1,// 当前进度
                'error' => $task->status === Task::STATUS_ERROR ? ($task->status_info ? $task->status_info : '任务报错') : '', // 错误信息
            ];
        }
        $subs['progress'] = $sub_has_error ? self::PROGRESS_ERROR : ($sub_has_ing ? self::PROGRESS_ING : self::PROGRESS_OK);
        return $subs;
    }

}