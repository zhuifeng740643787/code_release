<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/14
 * Time: 上午9:25
 */
namespace App\Model;

class TaskProjectReplaceFile extends BaseModel {

    protected $table = 'task_project_replace_file';

    const TYPE_STATIC = 1; // 文件类型 1=静态文件
    const TYPE_UPLOAD = 2; // 文件类型 2=上传文件

    /**
     * @param int $task_project_id
     * @param int $type
     * @param bool $ret_arr 是否返回数组类型
     * @return array
     */
    public static function getFiles($task_project_id = 0, $type = 0, $ret_arr = true) {
        $where = '';
        $params = [];
        if ($task_project_id) {
            $where .= " task_project_id=:TASK_PROJECT_ID ";
            $params[':TASK_PROJECT_ID'] = $task_project_id;
        }
        if ($type) {
            $where .= empty($where) ? " type=:TYPE" : " and type=:TYPE";
            $params[':TYPE'] = $type;
        }
        $files = self::getInstance()->select('*', $where, $params);
        if ($ret_arr) {
            $files = array_map(function($item) {
                return [
                    "id" => $item->id,
                    "task_project_id" => $item->task_project_id,
                    "type" => $item->type,
                    "local_file" => $item->local_file,
                    "replace_file" => $item->replace_file,
                    "created_at" => $item->created_at,
                    "updated_at" => $item->updated_at,
                ];
            }, $files);
        }

        return $files;
    }
}
