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
    const TYPE_SUB = 2; // 2=子任务

}
