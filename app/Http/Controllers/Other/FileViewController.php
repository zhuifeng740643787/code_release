<?php
namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class FileViewController extends Controller
{
    public function index(Request $request, Response $response)
    {

        $file_name = $request->get('file_name', '');
        if (!$file_name) {
            return $response->jsonError('文件名称有误');
        }
        $upload_file_path = $request->app->config->get('app.upload_file_path');
        $file = $upload_file_path . DS . $file_name;
        if (!file_exists($file) || !is_readable($file)) {
            return $response->jsonError('文件不存在或不可读');
        }
        return $response->jsonSuccess([
            'content' => file_get_contents($file)
        ]);
    }
}