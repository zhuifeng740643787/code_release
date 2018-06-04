<?php

namespace Deployer;

define('TASK_SUCCESS', 1);
define('TASK_ERROR', 0);

$deploy_config = include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR  . 'deploy.php';
foreach ($deploy_config as $key => $value) {
    set($key, $value);
}
//set('repository', 'xxx'); // 仓库地址
//set('project_name', 'xxx'); // 项目名称, 也用作项目的目录名称
//set('branch', 'xxx'); // 分支名称
//set('local_git_path', '/usr/local/bin/git'); // git命令地址
//set('local_zip_path', '/usr/bin/zip'); // zip命令地址
//set('remote_unzip_path', '/usr/bin/unzip'); // unzip命令地址
//set('remote_host', 'xxx'); // 远程服务器IP
//set('remote_user', 'xxx'); // 登录远程服务器的用户名称
//set('remote_code_release_path', '/acs/code/releases'); // 远程服务器的项目存放路径
set('datetime', date('YmdHis')); // 时间
set('local_tmp_code_path', '../storage/tmp/code'); // 项目临时目录
set('local_tmp_zip_path', '../storage/tmp/zip');// 项目压缩后存放的临时目录
set('local_tmp_log_file', '../storage/tmp/log/release.log');// 发布日志
set('identity_file_path', '../config/ssh/id_rsa'); // ssh私钥

// Hosts
localhost()->stage('local');

// Hosts
host('server')
    ->hostname(get('remote_host'))
    ->user(get('remote_user'))
    ->identityFile(get('identity_file_path'));

// Tasks, 本地执行
task('local_prepare', [
    'get_branch_code',
    'zip_code',
    'up_code',
]);
// 服务器端执行
task('server_release', [
    'unzip_code'
]);

// 初始化仓库代码,有代码的话跳过，拉取master分支
task('init_repository_code', function() {
    writeLog('init_repository_code start');
    $git = get('local_git_path');
    $code_path = realpath(get('local_tmp_code_path')) . DIRECTORY_SEPARATOR . get('project_name');
    if (!file_exists($code_path)) {
        run("mkdir -p $code_path");
    }
    // 判断是否有代码, 没有则拉取master分支的代码
    if (!file_exists($code_path . DIRECTORY_SEPARATOR . '.git')) {
        run("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone {{repository}} $code_path");
    }
    writeLog('init_repository_code end');
});

// 获取仓库的所有分支
task('get_repository_branches', function() {
    writeLog('get_repository_branches start');
    $git = get('local_git_path');
    $code_path = realpath(get('local_tmp_code_path')) . DIRECTORY_SEPARATOR . get('project_name');
    if (!file_exists($code_path)) {
        run("mkdir -p $code_path");
    }
    // 判断是否有代码, 没有则拉取master分支的代码
    if (!file_exists($code_path . DIRECTORY_SEPARATOR . '.git')) {
        run("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone {{repository}} $code_path");
    }

    echo run("cd $code_path && $git branch -a");
    writeLog('get_repository_branches end');
});


// 获取分支最新代码
task('get_branch_code', function () {
    writeLog('get_branch_code start');
    $git = get('local_git_path');
    $code_path = realpath(get('local_tmp_code_path')) . DIRECTORY_SEPARATOR . get('project_name');
    if (!file_exists($code_path)) {
        run("mkdir -p $code_path");
    }
    // 判断是否有代码, 没有则拉取master分支的代码
    if (!file_exists($code_path . DIRECTORY_SEPARATOR . '.git')) {
        run("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone {{repository}} $code_path");
    }

    // 需要拉取的分支
    $branch = str_replace('remotes/origin/', '', get('branch'));
    // 判断本地是否已含有所要拉取的分支
    $local_branches = trim(run("cd $code_path && $git branch --column"));
    $local_branch_arr = explode(' ', $local_branches);

    // 当前分支名称
    $current_branch = $local_branch_arr[array_search('*', $local_branch_arr)+1];
    // 是当前分支，拉取最新代码
    if ($current_branch == $branch) {
        run("cd $code_path && $git pull");
        writeLog('get_branch_code end');
        echo TASK_SUCCESS;
        return;
    }

    // clean当前分支的代码,以便切换分支
    if (!checkBranchClean($code_path, $git)) {
        run("cd $code_path && $git add -A && $git stash");
    }
    echo $branch;
    // 判断是否存在于本地分支
    if (!in_array($branch, $local_branch_arr)) {
        run("cd $code_path && $git checkout remotes/origin/$branch -b $branch");
    } else {
        run("cd $code_path && $git checkout $branch && $git pull");
    }
    echo TASK_SUCCESS;
    writeLog('get_branch_code end');
});

// 压缩代码
task('zip_code', function () {
    writeLog('zip_code start');
    $code_path = realpath(get('local_tmp_code_path')) . DIRECTORY_SEPARATOR . get('project_name');
    $zip_file = basename($code_path);
    $zip = realpath(get('local_zip_path'));
    $zip_file_name = realpath(get('local_tmp_zip_path')) . DIRECTORY_SEPARATOR . getZipFileName();
    run("cd $code_path/../ && $zip -r $zip_file_name $zip_file --exclude \\*.log");
    writeLog('zip_code end');
});

task('up_code', function () {
    writeLog('up_code start');
    $local_tmp_zip_path = realpath(get('local_tmp_zip_path'));
    $zip_file_name = getZipFileName();
    $cp_zip_file = $local_tmp_zip_path . DIRECTORY_SEPARATOR . $zip_file_name;
    $identity_file_path = realpath(get('identity_file_path'));
    $remote_ssh = get('remote_user') . '@' . get('remote_host');
    $remote_code_release_path = get('remote_code_release_path');
    run("scp -i $identity_file_path $cp_zip_file $remote_ssh:$remote_code_release_path", ['timeout' => 600, 'tty' => true]);
    // 将要发布的zip名称写入日志，且传到远程服务器
    $release_txt_file = $local_tmp_zip_path . DIRECTORY_SEPARATOR . 'release.txt';
    file_put_contents($release_txt_file, $zip_file_name);
    run("scp -i $identity_file_path $release_txt_file $remote_ssh:$remote_code_release_path", ['timeout' => 60, 'tty' => true]);
    writeLog('up_code end');
});

task('unzip_code', function() {
    $remote_code_release_path = get('remote_code_release_path');
    $release_txt_file = $remote_code_release_path . '/release.txt';
    // 判断文件是否存在
    if (!test("[[ -e $release_txt_file ]]")) {
        echo '文件不存在';
        return;
    }
    // 临时目录
    $tmp_dir = $remote_code_release_path . '/' . get('project_name') . '_release_tmp';
    if (!test("[[ -e $tmp_dir ]]")){
        run("mkdir $tmp_dir");
    }
    // 先将代码放到临时目录
    $zip_file = run("cd $tmp_dir && cat $release_txt_file");
    $zip_file_path = $remote_code_release_path . '/' . $zip_file;
    // 判断zip文件是否存在
    if (!test("[[ -e $zip_file_path ]]")) {
        return;
    }
    run("cd $tmp_dir && unzip $zip_file_path");
    // 移动代码未见
    $project_name = get('project_name');
    $project_path = $remote_code_release_path . '/' . $project_name;
    $tmp_project_path = $tmp_dir . '/' . $project_name;

    cd($remote_code_release_path);
    if (test("[[ -e $project_path ]]")) {
        run("mv $project_path {$project_path}_old && mv $tmp_project_path $project_path && rm -f {$project_path}_old");
    } else {
        run("mv $tmp_project_path $project_path");
    }
    // 删除压缩包
    run("rm -f $zip_file_path");
});
// 获取压缩的文件
function getZipFileName() {
    return get('project_name') . '_' . get('branch') . '_' . get('datetime') . '.zip';
}

/**
 * 判断分支是否有未提交的代码
 * @param $code_path
 * @param $git
 * @return bool
 */
function checkBranchClean($code_path, $git) {
    $status = run("cd $code_path && $git status");
    return strpos($status, 'working tree clean') !== false;
}

/**
 * 写入日志
 * @param $message
 */
function writeLog($message) {
    if (is_array($message)) {
        $message = json_encode($message);
    }
    $message = date('Y-m-d H:i:s ') . get('project_name') . ' ' . get('branch') . ' ' . $message . PHP_EOL;
    file_put_contents(get('local_tmp_log_file'), $message, FILE_APPEND);
}
