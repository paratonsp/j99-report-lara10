<?php

namespace Modules\Pariwisata\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PariwisataController extends Controller
{
    public function index()
    {
        $data['title'] = 'REPORT PARIWISATA';
        return view('pariwisata::index', $data);
    }
}
