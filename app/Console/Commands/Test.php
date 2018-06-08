<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */
namespace App\Console\Commands;

use App\Helper\Utils;

class Test extends Command
{

    public static $name = 'test';
    public static $description = '测试';

    public function run()
    {
        $task_file = '/Users/gongyidong/workspace/baison/code_release/storage/tmp/task/task_mid_src_version_20180608143056__host__千色店91';

        // 先锁住文件
        $file_handle = fopen($task_file, 'r+');
        // 获取文件独占锁且不阻塞
        if (!flock($file_handle, LOCK_EX |LOCK_NB)) {
            echo 'not';
            fclose($file_handle);
            return false;
        }

        // 处理任务
        sleep(5);
        echo 'haha';
        flock($file_handle, LOCK_UN);
        unlink($task_file);
    }


}