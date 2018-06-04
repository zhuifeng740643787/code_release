<?php
namespace App\Http\Controllers\Other;

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

        $upload_file_path = $request->app->config->get('app.upload_file_path');
        if (!move_uploaded_file($file['tmp_name'], $upload_file_path . DS . $file['name'])) {
            return $response->jsonError('上传有误');
        }
        return $response->jsonSuccess([
            'file_name' => $file['name']
        ]);
    }
}