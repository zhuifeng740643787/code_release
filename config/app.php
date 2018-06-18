<?php

return [
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