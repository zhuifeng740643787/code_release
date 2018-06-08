<?php

return [
    // 主机列表
    'hosts' => [
        'server1' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ],
        'server2' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ],
        'server3' => [
            'host' => '127.0.0.1',
            'user' => 'root'
        ]
    ],
    'repositories' => [
        'rep1' => [
            'address' => 'git@xxxx/xxx.git', // 仓库地址
            'static_files' => [ // 静态文件列表，发布新版本后，在老版本中直接复制过来
                'xx/xx', // 项目目录下的文件
            ],
        ],
        'rep2' => [
            'address' => 'git@xxxx/xxx.git', // 仓库地址
            'static_files' => [ // 静态文件列表，发布新版本后，在老版本中直接复制过来
                'xx/xx', // 项目目录下的文件
            ],
        ],
    ],
    'upload_file_path' => PROJECT_ROOT . DS . 'storage' . DS . 'tmp' . DS . 'upload', // 上传文件的路径
    'log_path' => PROJECT_ROOT . DS . 'storage' . DS . 'app' . DS . 'log', // 日志路径
];