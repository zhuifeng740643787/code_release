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

}
