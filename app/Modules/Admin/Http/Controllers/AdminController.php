<?php

namespace App\Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        echo 'admin下面的index方法';
    }
}
