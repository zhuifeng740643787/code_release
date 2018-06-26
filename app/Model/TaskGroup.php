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
 * @package App\Model
 * @property int id
 * @property string version_num
 * @property int status
 * @property int prev_status
 * @property string status_info
 * @property string release_code_path
 * @property string remark
 * @property string params
 */
class TaskGroup extends BaseModel
{

    protected $table = 'task_group';

    const STATUS_ERROR = -10; // 状态 -10=任务报错
    const STATUS_CANCELED = 0; // 状态 0=已取消
    const STATUS_CREATED = 10; // 状态 10=任务创建
    const STATUS_STARTED = 20; // 状态 20=开始任务
    const STATUS_COPIED = 30; // 状态 30=代码复制完成
    const STATUS_PACKED = 40; // 状态 40=打包完成
    const STATUS_SUB_PROCESSING = 50; // 状态 50=子任务进行中
    const STATUS_FINISHED = 60; // 状态 60=完成

    // 步骤
    public static $status_steps = [
        self::STATUS_CREATED,
        self::STATUS_STARTED,
        self::STATUS_COPIED,
        self::STATUS_PACKED,
        self::STATUS_SUB_PROCESSING,
        self::STATUS_FINISHED,
    ];

    // 状态映射
    public static $status_map = [
        self::STATUS_ERROR => '任务报错',
        self::STATUS_CANCELED => '已取消',
        self::STATUS_CREATED => '任务创建',
        self::STATUS_STARTED => '开始任务',
        self::STATUS_COPIED => '代码复制完成',
        self::STATUS_PACKED => '打包完成',
        self::STATUS_SUB_PROCESSING => '子任务进行中',
        self::STATUS_FINISHED => '完成'
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
        TaskLog::addLog($this->id, $status);
        return $this;
    }

}
