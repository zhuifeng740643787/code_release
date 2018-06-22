<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

class TaskLog extends BaseModel {

    protected $table = 'task_log';

    const TYPE_GROUP = 1; // 任务类型 1=组任务
    const TYPE_SUB   = 2; // 任务类型 2=子任务

    /**
     * 写入日志
     * @param $task_group_id
     * @param $status
     * @param null $task_id
     * @return static
     */
    public static function addLog($task_group_id, $status, $task_id = null) {
        $params = [
            'task_group_id' => $task_group_id,
            'type' => $task_id ? self::TYPE_SUB : self::TYPE_GROUP,
            'status' => $status,
        ];
        if ($task_id) {
            $params['task_id'] = $task_id;
        }
        return self::add($params);
    }


}
