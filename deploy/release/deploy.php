<?php

namespace Deployer;
require 'recipe/common.php';

ini_set('date.timezone', 'Asia/Shanghai');

define('DS', DIRECTORY_SEPARATOR);
define('TASK_SUCCESS', 1); // 操作成功
define('TASK_ERROR', 0);
define('TASK_NO_ZIP_FILE', -1); //无zip文件

set('ssh_type', 'native');
set('ssh_multiplexing', true);

$deploy_config = include_once 'config.php';
$remote_servers = $deploy_config['remote_servers'];

// 设置变量
foreach ($deploy_config as $key => $value) {
    set($key, $value);
}

// 设置服务器
foreach ($remote_servers as $server) {
    server($server['name'], $server['host'], $server['port'])
        ->user($server['user'])
        ->password($server['password'])
        ->identityFile(get('identity_file_path'));
}

// 解压代码
// 1=成功
task('unzip_and_deploy_code', function() {
    $remote_code_release_path = get('remote_code_release_path'); // 路径
    $version_num = get('version_num'); // 版本名称
    // 进入目录
    cd($remote_code_release_path);
    $zip_file = $version_num. '.zip';
    $zip_file_path = $remote_code_release_path . '/' . $zip_file;
    // 判断zip文件是否存在
    if (!test("[[ -e $zip_file_path ]]")) {
        echo TASK_NO_ZIP_FILE;
        exit;
    }
    $unzip_bin = get('remote_unzip_bin');
    // 先删掉存在的相同的代码目录 解压到以版本号命名的目录
    run("rm -rf $version_num && $unzip_bin $zip_file_path -d $version_num");
    // 移动代码文件
    $projects = get('projects');
    $version_suffix = '_v' . date('YmdHis');
    foreach ($projects as $project) {
        $project_name = $project['name'];
        $origin_project_path = $remote_code_release_path . '/' . $project_name;
        $new_project_path = $remote_code_release_path . '/' . $version_num . '/' . $project_name;
        if (!test("[[ -e $new_project_path ]]")) {
            continue;
        }

        $version_project_name = $origin_project_path . $version_suffix;
        // 判断是否有已存在的项目文件，有则将一些静态配置文件覆盖到当前项目
        if (test("[[ -e $origin_project_path ]]")) {
            $static_files = $project['static_files'];
            foreach ($static_files as $static_file){
                if (test("[[ -e $origin_project_path\/$static_file ]]")) {
                    run("cp $origin_project_path\/$static_file $new_project_path\/$static_file && echo 1");
                }
            }
            run("mv $origin_project_path {$version_project_name} && mv $new_project_path $origin_project_path && echo 1");
        } else {
            run("mv $new_project_path $origin_project_path && echo 1");
        }
    }

    // 删除压缩包
    run("rm -rf $version_num && rm -f $zip_file_path && echo 1");
    echo TASK_SUCCESS;
    exit;
});

// 保留版本数,最少三个
task('remain_history_version', function() {
    $remote_code_release_path = get('remote_code_release_path');
    cd($remote_code_release_path);
    $remain_history_version_num = max(3, intval(get('remain_history_version_num')));
    $projects = get('projects');
    foreach ($projects as $project) {
        $project_name = $project['name'];
        $find_dirs = run("find . -maxdepth 1 -name '{$project_name}_v*'");
        $dir_arr = explode("\n",$find_dirs);
        // 去除zip文件
        foreach ($dir_arr as $key => $dir) {
            if (strpos($dir, '.zip') > 0) {
                unset($dir_arr[$key]);
            }
        }
        if (count($dir_arr) <= $remain_history_version_num) {
            echo TASK_SUCCESS;
            exit;
        }

        // 先排序
        sort($dir_arr, SORT_STRING);
        // 删除，只保留$remain_history_version_num个版本
        $remove_dirs = array_splice($dir_arr, 0, count($dir_arr)-$remain_history_version_num);
        foreach($remove_dirs as $dir) {
            $rm_dir = $remote_code_release_path . ltrim( $dir, '.');
            // 做个防范, 删除需谨慎
            if (strpos($rm_dir, $project_name . '_v') === false) {
                continue;
            }
            run("rm -rf $rm_dir");
        }
    }

    echo TASK_SUCCESS;
    exit;
});

