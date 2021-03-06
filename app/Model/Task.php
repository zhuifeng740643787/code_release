<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */

namespace App\Model;

/**
 * Class Task
 * @package App\Model
 * @property int id
 * @property int status
 * @property int prev_status
 * @property string status_info
 * @property int task_group_id
 */
class Task extends BaseModel
{

    protected $table = 'task';

    const STATUS_ERROR = -10; //状态:-10=任务报错
    const STATUS_CANCELED = 0; // 0=已取消
    const STATUS_CREATED = 10; // 10=任务创建
    const STATUS_UPLOADED = 20; // 20=已上传至服务器
    const STATUS_DEPLOYED = 30; // 30=已解压并部署
    const STATUS_FINISHED = 40; // 40=完成（已保留版本）

    public static $status_steps = [
        self::STATUS_CREATED,
        self::STATUS_UPLOADED,
        self::STATUS_DEPLOYED,
        self::STATUS_FINISHED,
    ];

    public static $status_map = [
        self::STATUS_ERROR => '任务报错',
        self::STATUS_CANCELED => '已取消',
        self::STATUS_CREATED => '任务创建',
        self::STATUS_UPLOADED => '已上传至服务器',
        self::STATUS_DEPLOYED => '已解压并部署',
        self::STATUS_FINISHED => '完成（已保留版本）',
    ];

    /**
     * 修改状态,并写入日志
     * @param $status
     * @param string $status_info
     * @return mixed
     */
    public function changeStatus($status, $status_info = '')
    {
        if ($this->status == $status) {
            return $this;
        }
        $this->prev_status = $this->status;
        $this->status = $status;
        $this->status_info = $status_info;
        $this->save();
        // 添加日志
        TaskLog::addLog($this->task_group_id, $status, $this->id);
        return $this;
    }


}
