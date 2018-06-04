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
    'local_git_path' => '/usr/local/bin/git', // git命令地址
    'local_zip_path' => '/usr/bin/zip', // zip命令地址
    'remote_unzip_path' => '/usr/bin/unzip', // unzip命令地址
    'local_dep_path' => '/usr/local/bin/dep', // dep命令地址
    'remote_host' => '', // 远程服务器IP
    'remote_user' => '', // 登录远程服务器的用户名称
    'remote_code_release_path' => '/acs/code/releases', // 远程服务器的项目存放路径
];
