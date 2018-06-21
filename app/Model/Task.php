<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

class Task extends BaseModel {

    protected $table = 'task';

    const STATUS_ERROR = -10; //状态:-10=任务报错
    const STATUS_CANCELED = 0; // 0=已取消
    const STATUS_CREATED = 10; // 10=任务创建
    const STATUS_UPLOADED = 20; // 20=已上传至服务器
    const STATUS_DEPLOYED = 30; // 30=已解压并部署
    const STATUS_FINISHED = 40; // 40=完成（已保留版本）

}
