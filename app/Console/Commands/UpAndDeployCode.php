<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午4:11
 */
namespace App\Console\Commands;

use App\Helper\Utils;

class UpAndDeployCode extends Command
{

    public static $name = 'up_and_deploy_code';
    public static $description = '上传且部署代码(服务器)';

    protected $sleep_time = 3;// 间隔3秒
    public function run()
    {
        Utils::log('------------- up_and_deploy_code start --------------');
        $deploy_config = $this->app->config->get('deploy');
        $local_tmp_release_path = $deploy_config['local_tmp_release_path'];
        // 判断是否有任务
        $last_release_file = $local_tmp_release_path . DS . $deploy_config['last_release_log_file'];
        if(!file_exists($last_release_file)) {
            Utils::log('------------- up_and_deploy_code 发布日志文件不存在 --------------');
            exit;
        }
        // 判断是否有任务
        if (!is_readable($last_release_file)) {
            Utils::log('------------- up_and_deploy_code 发布日志文件不可读 --------------');
            throw new \Exception('发布日志文件不可读');
        }

        $file_handle = fopen($last_release_file, 'r+');
        $would_block = true;
        // 文件加锁
        if (flock($file_handle, LOCK_EX, $would_block)) {
            // 处理任务
            $release_file = trim(fread($file_handle, 1024));
            if (!empty($release_file)) {
                $release_code = $local_tmp_release_path . DS . $release_file;
                if (file_exists($release_code)) {
                    Utils::log('------------- up_and_deploy_code 处理任务中 '.$release_file.'--------------');
                    $this->handleRelease();
                    Utils::log('------------- up_and_deploy_code 处理任务完成 --------------');
                }
                // 将文件清空
                ftruncate($file_handle, 0);
            } else {
                Utils::log('------------- up_and_deploy_code 无任务 --------------');
            }

            flock($file_handle, LOCK_UN);
        } else {
            Utils::log('------------- up_and_deploy_code 任务当前在被处理 --------------');
        }

        fclose($file_handle);

        Utils::log('------------- up_and_deploy_code end --------------');
    }


    // 处理发布
    protected function handleRelease() {
        Utils::runDep('zip_and_up_code', 'local');
        Utils::runDep('server_release', 'server');
    }


}