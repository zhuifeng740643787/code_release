<?php
namespace App\Http\Controllers\Home;

use App\Helper\Code;
use App\Http\Controllers\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\View;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        View::render('home');
    }
}