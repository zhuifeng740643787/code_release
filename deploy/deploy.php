<?php

namespace Deployer;
require 'recipe/common.php';

define('DS', DIRECTORY_SEPARATOR);
define('TASK_SUCCESS', 1);
define('TASK_ERROR', 0);

set('ssh_type', 'native');
set('ssh_multiplexing', true);

$deploy_config = include_once 'config.php';
$remote_servers = $deploy_config['remote_servers'];

// 设置变量
foreach ($deploy_config as $key => $value) {
    set($key, $value);
}

// 设置服务器
// 本地
localServer('local')->stage('local');
// 服务器组
foreach ($remote_servers as $server_name => $server) {
    server($server_name, $server['host'])
        ->user($server['user'])
        ->identityFile(get('identity_file_path'));
}

// Tasks, 本地执行
task('zip_and_up_code', [
    'zip_code',
    'up_code',
]);

// 服务器端执行
task('server_release', [
    'unzip_and_deploy_code',
    'remain_history_version'
]);

// 上传代码
task('up_code', function () {
    writeLog('up_code start');
    $local_tmp_zip_path = realpath(get('local_tmp_zip_path'));
    $last_release_file = getLastReleaseFile();
    $zip_file_name = $last_release_file . '.zip';

    $cp_zip_file = $local_tmp_zip_path . DIRECTORY_SEPARATOR . $zip_file_name;
    $identity_file_path = realpath(get('identity_file_path'));
    $remote_servers = get("remote_servers");
    // 要发版的服务器信息
    if (!isset($remote_servers[get('release_server_name')])) {
        writeLog('up_code error: servername not find');
        echo TASK_ERROR;
        return TASK_ERROR;
    }
    $remote_server = $remote_servers[get('release_server_name')];
    $remote_ssh = $remote_server['user'] . '@' . $remote_server['host'];
    $remote_code_release_path = get('remote_code_release_path');

    // 检查并创建代码目录
    run("ssh $remote_ssh '[ -d $remote_code_release_path ] && echo 1 || mkdir -p $remote_code_release_path'");
    // 上传代码
    run("scp -i $identity_file_path $cp_zip_file $remote_ssh:$remote_code_release_path", ['timeout' => 600, 'tty' => true]);
    writeLog('up_code end');
    echo TASK_SUCCESS;
    return TASK_SUCCESS;
});

// 解压代码
task('unzip_and_deploy_code', function() {
    $remote_code_release_path = get('remote_code_release_path');
    cd($remote_code_release_path);
    $release_file = get("release_project_name");
    $zip_file = $release_file . '.zip';
    $zip_file_path = $remote_code_release_path . '/' . $zip_file;
    // 判断zip文件是否存在
    if (!test("[[ -e $zip_file_path ]]")) {
        throw new \Exception('压缩文件不存在');
    }
    $unzip_bin = get('remote_unzip_bin');
    // 先删掉存在的相同的代码目录 解压
    run("rm -rf $release_file && $unzip_bin $zip_file_path");
    // 移动代码文件
    $project_name = get('project_name');
    $project_path = $remote_code_release_path . '/' . $project_name;

    // 判断是否有已存在的项目文件，有则将一些静态配置文件覆盖到当前项目
    if (test("[[ -e $project_path ]]")) {
        foreach (get('static_files') as $static_file){
            if (test("[[ -e $project_path\/$static_file ]]")) {
                run("cp $project_path\/$static_file $release_file\/$static_file");
            }
        }
    }

    $version_project_name = $project_path . '_v'.date('YmdHis');
    if (test("[[ -e $project_path ]]")) {
        run("mv $project_path {$version_project_name} && mv $release_file $project_path");
    } else {
        run("mv $release_file $project_path");
    }
    // 删除压缩包
    run("rm -f $zip_file_path");

    echo TASK_SUCCESS;
    return TASK_SUCCESS;
});

// 保留版本数,最少三个
task('remain_history_version', function() {
    $remote_code_release_path = get('remote_code_release_path');
    $project_name = get('project_name');
    cd($remote_code_release_path);
    $remain_history_version_num = max(3, intval(get('remain_history_version_num')));
    $find_dirs = run("find . -maxdepth 1 -name \"{$project_name}_v*\" |grep -v 'zip'");
    $dir_arr = explode("\n",$find_dirs);
    if (count($dir_arr) <= $remain_history_version_num) {
        echo TASK_SUCCESS;
        return TASK_SUCCESS;
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

    echo TASK_SUCCESS;
    return TASK_SUCCESS;
});

