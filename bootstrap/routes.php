<?php
/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:40
 */

return [
    '/' => \App\Http\Controllers\Home\IndexController::class,

    '/other/config' => \App\Http\Controllers\Other\ConfigController::class,
    '/other/upload' => \App\Http\Controllers\Other\UploadController::class,
    '/other/file/view' => \App\Http\Controllers\Other\FileViewController::class,
    '/other/file/change' => \App\Http\Controllers\Other\FileChangeController::class,

    '/git/branches' => \App\Http\Controllers\Git\BranchController::class,
    '/git/tags' => \App\Http\Controllers\Git\TagController::class,

    '/release' => \App\Http\Controllers\Release\IndexController::class,
    '/release/progress' => \App\Http\Controllers\Release\ProgressController::class,

];