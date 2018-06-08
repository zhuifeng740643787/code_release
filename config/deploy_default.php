<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/22
 * Time: 下午5:26
 */
return [
    'repository' => '', // 仓库地址
    'project_name' => '', // 项目名称, 也用作项目的目录名称
    'branch' => 'master', // 分支名称
    'local_git_bin' => '/usr/local/bin/git', // git命令地址
    'local_zip_bin' => '/usr/bin/zip', // zip命令地址
    'remote_unzip_bin' => '/usr/bin/unzip', // unzip命令地址
    'local_dep_bin' => '/usr/local/bin/dep', // dep命令地址
    'remote_servers' => [
        'server1' => [
            'host' => '', // 远程服务器IP
            'user' => '', // 登录远程服务器的用户名称
        ],
    ],
    'remote_code_release_path' => '/acs/code/releases', // 远程服务器的项目存放路径
    'local_tmp_task_path' => TMP_ROOT . DS . 'task', // 任务目录
    'local_tmp_code_path' => TMP_ROOT . DS . 'code', // 项目临时目录
    'local_tmp_zip_path' => TMP_ROOT . DS . 'zip',// 项目压缩后存放的临时目录
    'local_tmp_release_path' => TMP_ROOT . DS . 'release', // 项目发布时的目录
    'local_tmp_log_file' => TMP_ROOT . DS . 'log' . DS . 'release.log', // 发布相关的日志文件
    'last_release_log_file' => 'last_release_file.log', // 存放当前要发布的项目+版本的文件
    'identity_file_path' => CONFIG_ROOT . DS . 'ssh' . DS . 'id_rsa', // ssh私钥
    'remain_history_version_num' => 3, // 保留历史版本的个数
    'static_files' => [], // 保留历史版本的文件
    'release_project_name' => '', // 要发版的项目文件名称(带版本号)
    'release_server_name' => '', // 要发版到的服务器
];
