<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

/**
 * Class TaskGroup
 * @params remark
 * @package App\Model
 */
class TaskGroup extends BaseModel {

    protected $table = 'task_group';

    const STATUS_ERROR = -10; // 状态 -10=任务报错
    const STATUS_CANCELED = 0; // 状态 0=已取消
    const STATUS_CREATED = 10; // 状态 10=任务创建
    const STATUS_STARTED = 20; // 状态 20=开始任务
    const STATUS_COPIED = 30; // 状态 30=代码复制完成
    const STATUS_PACKED = 40; // 状态 40=打包完成
    const STATUS_SUB_PROCESSING = 50; // 状态 50=子任务进行中
    const STATUS_FINISHED = 60; // 状态 60=完成

}
