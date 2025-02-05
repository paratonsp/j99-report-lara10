<?php

namespace Modules\Cms\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Cms;

class CmsController extends Controller
{
    public function listAddress()
    {
        $data['title'] = 'Alamat';
        $data['list'] = Cms::getAddress();

        return view('cms::address.index', $data);
    }

    public function addAddress()
    {
        $data['title'] = 'Tambah Alamat';

        return view('cms::address.edit', $data);
    }
}
