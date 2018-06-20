<?php
namespace App\Http\Controllers\Other;

use App\Helper\Utils;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class UploadController extends Controller
{
    public function index(Request $request, Response $response)
    {

        $file = $_FILES['upload_file'];
        if (empty($file) || $file['error'] != 0) {
            return $response->jsonError('上传有误');
        }
        $file_dir = rtrim($request->get('file_dir', ''), "\/");
        $upload_file_path = rtrim($request->app->config->get('app.upload_file_path'), "\/");
        $today = date('Ymd');
        $file_path =  $today . (empty($file_dir) ? '' : DS . $file_dir);
        $save_dir =  $upload_file_path . DS . $file_path;
        if (!file_exists($save_dir)) {
            $ret = Utils::runExec("mkdir -p $save_dir");
            if ($ret === false) {
                return $response->jsonError('无法保存文件，请检查权限');
            }
        }
        if (!move_uploaded_file($file['tmp_name'], $save_dir . DS . $file['name'])) {
            return $response->jsonError('上传有误');
        }
        return $response->jsonSuccess([
            'file_name' => $file_path . DS . $file['name']
        ]);
    }
}