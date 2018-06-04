<?php
namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class FileChangeController extends Controller
{
    public function index(Request $request, Response $response)
    {

        $file_name = $request->get('file_name', '');
        $content = $request->get('content', '');
        if (!$file_name) {
            return $response->jsonError('文件名称有误');
        }
        $upload_file_path = $request->app->config->get('app.upload_file_path');
        $file = $upload_file_path . DS . $file_name;
        if (!file_exists($file) || !is_writable($file)) {
            return $response->jsonError('文件不存在或不可写');
        }
        if (false === file_put_contents($file, $content)) {
            return $response->jsonError('文件修改失败');
        }
        return $response->jsonSuccess();
    }
}