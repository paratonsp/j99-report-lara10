<?php

namespace Modules\UserManagement\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Menu;
use App\Models\Role;

class MenuController extends Controller
{
    public function listMenu()
    {
        $data['title'] = 'Menu';
        $data['list'] = Menu::getMenu();

        return view('usermanagement::menu.index', $data);
    }

    public function addMenu()
    {
        $data['title'] = 'Tambah Menu';
        $data['parents'] = Menu::getMenuParent();
        $data['access'] = Role::GetAccess();
        return view('usermanagement::menu.add', $data);
    }
    
    public function addMenuStore(Request $request)
    {
        $credentials = $request->validate([
            'menuname' => ['required', 'string'],
            'slug' => ['required', 'string'],
            'urllink' => ['required', 'string'],
            'module' => ['required', 'string'],
            'parent' => ['required', 'string'],
            'order' => ['required', 'string'],
            'icon' => ['required', 'string'],
        ]);

        $permData = [];
        $slug = sluggify($request->slug);
        $parent = $request->parent !== '-' ? $request->parent : NULL;

        $create = Menu::create([
            'title' => $request->menuname,
            'url' => $request->urllink,
            'module' => $request->module,
            'parent_id' => $parent,
            'order' => $request->order,
            'icon' => $request->icon,
            'slug' => $slug,
            'status' => 1,
        ]);

        if ($parent === NULL) {
            $permData[0]['slug'] = $slug;
            $permData[0]['access'] = 'index';
            $permData[0]['status'] = '1';
        } else {
            foreach ($request->access as $key => $value) {
                $permData[$key]['slug'] = $slug;
                $permData[$key]['access'] = $value;
                $permData[$key]['status'] = '1';
            }
        }

        $save = Menu::savePermission($permData);

        if ($create) {
            setUserSession(auth()->user()->email);
            return back()->with('success', 'Menu tersimpan!');
        }

        return back()->with('failed', 'Menu gagal tersimpan!');        
    }
}
