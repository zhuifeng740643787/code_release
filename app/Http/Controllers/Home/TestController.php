<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Lib\View;

class TestController extends Controller
{
    public function index()
    {
        View::render('home');
    }
}