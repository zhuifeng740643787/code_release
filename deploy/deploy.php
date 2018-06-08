<?php

namespace Deployer;

require 'recipe/common.php';

define('DS', DIRECTORY_SEPARATOR);
define('TASK_SUCCESS', 1);
define('TASK_ERROR', 0);

set('ssh_type', 'native');
set('ssh_multiplexing', true);

$deploy_config = include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR  . 'deploy.php';
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

// 初始化仓库代码,有代码的话跳过，拉取master分支
task('init_repository_code', function() {
    writeLog('init_repository_code start');
    $git = get('local_git_bin');
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
    $git = get('local_git_bin');
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
    exit;
});

// 获取仓库提交的head信息
task('get_git_commit_head', function() {
    writeLog('get_git_commit_head start');
    $git = get('local_git_bin');
    $code_path = realpath(get('local_tmp_code_path')) . DIRECTORY_SEPARATOR . get('project_name');
    if (!file_exists($code_path)) {
        run("mkdir -p $code_path");
    }
    // 判断是否有代码, 没有则拉取master分支的代码
    if (!file_exists($code_path . DIRECTORY_SEPARATOR . '.git')) {
        run("cd $code_path && rm -rf ./* && rm -rf ./.git && $git clone {{repository}} $code_path");
    }

    echo run("cd $code_path && $git log |head -n 1");
    writeLog('get_git_commit_head end');
    exit;
});

// 获取分支最新代码
task('get_branch_code', function () {
    writeLog('get_branch_code start');
    $git = get('local_git_bin');
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
        exit;
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
    exit;
});

// 压缩代码
task('zip_code', function () {
    writeLog('zip_code start');
    $release_path = realpath(get('local_tmp_release_path'));
    $last_release_file = getLastReleaseFile();

    if (false === $last_release_file) {
        throw new \Exception('没有要发布的代码');
    }
    $zip = realpath(get('local_zip_bin'));
    $zip_file_name = realpath(get('local_tmp_zip_path')) . DS . $last_release_file . '.zip';
    // 判断文件是否已存在（被其他任务执行过）
    if (!file_exists($zip_file_name)) {
        // 删除git目录，并压缩到zip目录
        run("cd $release_path && rm -rf $last_release_file" . DS . ".git && $zip -r $zip_file_name $last_release_file --exclude \\*.log");
    }
    writeLog('zip_code end');
    echo TASK_SUCCESS;
    return TASK_SUCCESS;
});

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

// 检查服务器是否可用
task('valid_server', function() {
    try {
        commandExist("pwd");
    } catch (\Exception $exception) {
        echo TASK_ERROR;exit;
    }
    echo TASK_SUCCESS;exit;
});

// 获取要发布的文件名
function getLastReleaseFile() {
    $release_project_name = get('release_project_name');
    $file = get('local_tmp_release_path') . DS . $release_project_name;
    if (!file_exists($file)) {
        throw new \Exception('要发布的文件不存在');
    }
    return $release_project_name;
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
