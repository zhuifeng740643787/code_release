<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

/**
 * Class TaskProject
 * @package App\Model
 * @property int id
 * @property int task_status
 * @property int prev_status
 */
class TaskProject extends BaseModel {

    protected $table = 'task_project';

    const RELEASE_TYPE_BRANCH = 1; // 发布类型 1=branch
    const RELEASE_TYPE_TAG = 2; // 发布类型 2=tag

    const TASK_STATUS_ERROR = -10; // 项目状态 -10=报错
    const TASK_STATUS_CREATED = 0; // 项目状态 0=创建完成
    const TASK_STATUS_COPIED = 10; // 项目状态 10=代码复制
    const TASK_STATUS_SWITCHED = 20; // 项目状态 20=切换分支/标签
    const TASK_STATUS_REPLACED = 30; // 项目状态 30=文件替换
    const TASK_STATUS_RELEASE_LOG = 40; // 项目状态 40=写入release日志文件

    public static $release_type_map = [
        self::RELEASE_TYPE_BRANCH => 'branch',
        self::RELEASE_TYPE_TAG => 'tag',
    ];

    /**
     * @param $status
     * @return mixed
     */
    public function changeStatus($status) {
        if ($this->task_status == $status) {
            return $this;
        }
        $this->prev_status = $this->task_status;
        $this->task_status = $status;
        $this->save();
        return $this;
    }

}
