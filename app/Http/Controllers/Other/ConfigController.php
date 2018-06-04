<?php
namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;

class ConfigController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $app_config = $request->app->config->get('app');

        return $response->jsonSuccess([
            'hosts' => $app_config['hosts'],
            'repositories' => $app_config['repositories'],
        ]);
    }
}