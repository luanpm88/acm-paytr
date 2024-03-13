<?php

namespace Acelle\Paytr\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;
use Acelle\Model\Plugin;
use Acelle\Paytr\Paytr;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        return view('paytr::index', [
            'paytr' => Paytr::initialize(),
        ]);
    }
}
