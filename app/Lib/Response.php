<?php
namespace App\Lib;
use App\Application;

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/5/16
 * Time: 下午2:42
 */
class Response
{
    public $app;

    /**
     * Response constructor.
     * @param $app Application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    // 成功
    public function jsonSuccess($data = []) {
        header('Content-type: application/json');
        echo json_encode([
            'status' => 'success',
            'result' => $data
        ]);
        exit;
    }

    // 失败
    public function jsonError($message) {
        header('Content-type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit;
    }



}