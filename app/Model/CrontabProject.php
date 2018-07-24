<?php

namespace App\Model;

class CrontabProject extends BaseModel {

    protected $table = 'crontab_project';

    const BRANCH_TYPE_BRANCH = 1; // 分支
    const BRANCH_TYPE_TAG = 2; // 标签


}
