<?php 
return array (
  'repository' => 'git@192.168.175.129:MC3/mc3.git',
  'project_name' => 'mc3',
  'use' => 'branch', // 使用branch还是tag
  'branch' => 'master',
  'tag' => '',
  'local_git_bin' => '/usr/local/bin/git',
  'local_zip_bin' => '/usr/bin/zip',
  'remote_unzip_bin' => '/usr/bin/unzip',
  'local_dep_bin' => '/usr/local/bin/dep',
  'remote_servers' => 
  array (
    'server1' => 
    array (
      'host' => '',
      'user' => '',
    ),
  ),
  'remote_code_release_path' => '/acs/code/releases',
  'local_tmp_task_path' => '/Users/gongyidong/workspace/baison/code_release/storage/tmp/task',
  'local_tmp_code_path' => '/Users/gongyidong/workspace/baison/code_release/storage/tmp/code',
  'local_tmp_zip_path' => '/Users/gongyidong/workspace/baison/code_release/storage/tmp/zip',
  'local_tmp_release_path' => '/Users/gongyidong/workspace/baison/code_release/storage/tmp/release',
  'local_tmp_log_file' => '/Users/gongyidong/workspace/baison/code_release/storage/tmp/log/release.log',
  'last_release_log_file' => 'last_release_file.log',
  'identity_file_path' => '/Users/gongyidong/workspace/baison/code_release/config/ssh/id_rsa',
  'remain_history_version_num' => 3,
  'static_files' => 
  array (
  ),
  'release_project_name' => '',
  'release_server_name' => '',
);