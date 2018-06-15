<?php

return [
//    // 主机列表
//    'hosts' => [
//        '够范185' => [
//            'host' => '120.27.133.185',
//            'user' => 'root'
//        ],
//        '千色店91' => [
//            'host' => '114.55.113.91',
//            'user' => 'root'
//        ],
//    ],
//    'repositories' => [
//        'mc3' => [
//            'address' => 'git@192.168.175.129:MC3/mc3.git', // 仓库地址
//            'static_files' => [ // 静态文件列表，发布新版本后，在老版本中直接复制过来
//                'config/database.php',
//            ],
//        ],
//        'mid_src' => [
//            'address' => 'git@192.168.175.129:POS/MID_SRC.git',
//            'static_files' => [ // 静态文件列表，发布新版本后，在老版本中直接复制过来
//                'saas.php',
//                'saas_config.php'
//                'config.php'
//            ],
//        ],
//        'mpos_online_src' => [
//            'address' => 'git@192.168.175.129:POS/MPOS_ONLNE/MPOS_ONLINE_SRC.git',
//            'static_files' => [ // 静态文件列表，发布新版本后，在老版本中直接复制过来
//                'script/saas.js',
//            ],
//        ]
//    ],
    'database' => [
        'mysql' => [
            'host' => env('HOST', '127.0.0.1'),
            'database' => env('DATABASE', 'code_release'),
            'user' => env('USER', 'root'),
            'password' => env('PASSWORD', '111111'),
            'charset' => 'utf8mb4',
            'options' => []
        ],
    ],
    'upload_file_path' => PROJECT_ROOT . DS . 'storage' . DS . 'tmp' . DS . 'upload', // 上传文件的路径
    'log_path' => PROJECT_ROOT . DS . 'storage' . DS . 'app' . DS . 'log', // 日志路径
];