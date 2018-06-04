<?php

return [
    // 主机列表
    'hosts' => [
        'host1' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ],
        'host2' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ],
        'host3' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ]
    ],
    'repositories' => [
        'rep1' => 'git@xxxx/xxx.git',
        'rep2' => 'git@xxxx/xxx.git',
        'rep3' => 'git@xxxx/xxx.git',
    ],
    'upload_file_path' => PROJECT_ROOT . DS . 'storage' . DS . 'tmp' . DS . 'upload', // 上传文件的路径
];