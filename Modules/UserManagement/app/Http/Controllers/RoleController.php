<?php

namespace Modules\UserManagement\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Menu;

class RoleController extends Controller
{
    public function listRole()
    {
        $data['title'] = 'Role';
        $data['list'] = Role::getRole();

        return view('usermanagement::role.index', $data);
    }

    public function addRole()
    {
        $data['title'] = 'Tambah Role';
        return view('usermanagement::role.add', $data);
    }
    
    public function addRoleStore(Request $request)
    {
        $credentials = $request->validate([
            'rolename'      => ['required', 'string'],
            'description'   => ['required', 'string'],
        ]);

        $create = Role::create([
            'uuid' => generateUuid(),
            'title' => $request->rolename,
            'slug' => sluggify($request->rolename),
            'description' => $request->description,
        ]);

        if ($create) {
            setUserSession(auth()->user()->email);
            return back()->with('success', 'Role tersimpan!');
        }

        return back()->with('failed', 'Role gagal tersimpan!');        
    }

    public function permissionForm($role_uuid)
    {
        $data['title'] = 'Role Permission';
        $data['role_info'] = Role::getRoleInfo($role_uuid);
        $permissionList = Menu::getMenu();
        foreach ($permissionList as $key => $value) {
            $access = Role::getAccess();
            $value->access = $access;
            foreach ($value->access as $keyAccess => $valueAccess) {
                $permission = Role::getPermission($value->slug, $valueAccess->name);
                $rolePermission = property_exists($permission, 'permid') ? Role::getRolePermission($permission->permid, $data['role_info']->id) : false;
                $valueAccess->permissionId = property_exists($permission, 'permid') ? $permission->permid : '-';
                $valueAccess->isAvailable = property_exists($permission, 'permid');
                $valueAccess->isGranted = $rolePermission;
            }
        }
        $data['permissionList'] = $permissionList;

        return view('usermanagement::role.permission', $data);
    }

    public function permissionStore(Request $request, $role_uuid)
    {
        $data = [];
        $roleId = Role::getRoleId($role_uuid);
        foreach ($request->permission as $key => $value) {
            $data[$key]['role_id'] = $roleId->roleid;
            $data[$key]['permission_id'] = $value;
        }

        $remove = Role::deleteRolePermission($roleId->roleid);
        $save = Role::saveRolePermission($data);
        
        if ($save) {
            setUserSession(auth()->user()->email);
            return back()->with('success', 'Akses role berhasil diubah!');
        }

        return back()->with('failed', 'Akses role gagal diubah!');        
    }
}
