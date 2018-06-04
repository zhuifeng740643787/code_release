<?php
namespace App\Http\Controllers\Release;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class IndexController extends Controller
{
    private $_params = [];
    public function index(Request $request, Response $response)
    {

        $this->initParams($request);
        return $response->jsonSuccess([
            'params' => $request->all()
        ]);
    }

    protected function initParams(Request $request) {
        $this->_params['branch'] = trim($request->get('branch', ''));
        $this->_params['host'] = trim($request->get('host', ''));
        $this->_params['project_name'] = trim($request->get('project_name', ''));
        $this->_params['project_path'] = trim($request->get('project_path', ''));
        $this->_params['repository'] = trim($request->get('repository', ''));
    }
}