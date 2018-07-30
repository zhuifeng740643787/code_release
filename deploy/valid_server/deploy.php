<?php

namespace Deployer;
require 'recipe/common.php';

ini_set('date.timezone', 'Asia/Shanghai');

set('ssh_type', 'native');
set('ssh_multiplexing', true);

const TASK_SUCCESS = 1;
const TASK_ERROR = 0;

// 设置服务器
$deploy_config = include 'config.php';
$remote_servers = $deploy_config['remote_servers'];
// 服务器组
foreach ($remote_servers as $server) {
    server($server['name'], $server['host'])
        ->user($server['user'])
        ->identityFile($deploy_config['identity_file_path']);
}

// 检查服务器是否可用
task('valid_server', function() {
    try {
        commandExist("pwd");
    } catch (\Exception $exception) {
        echo TASK_ERROR;exit;
    }
    echo TASK_SUCCESS;exit;
});
