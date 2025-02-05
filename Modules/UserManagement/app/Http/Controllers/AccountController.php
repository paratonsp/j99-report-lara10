<?php

namespace Modules\UserManagement\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AccountController extends Controller
{
    public function listAccount()
    {
        $data['title'] = 'Akun';
        $data['list'] = User::getUserList();

        return view('usermanagement::account.index', $data);
    }

    public function addAccount()
    {
        $data['title'] = 'Tambah Akun';
        $data['roles'] = Role::getRole();
        return view('usermanagement::account.add', $data);
    }
    
    public function addAccountStore(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required','email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', 'string'],
        ]);

        $create = User::create([
            'uuid' => generateUuid(),
            'name' => $request->name,
            'email' => $request->email,
            'role_uuid' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if ($create) {
            return back()->with('success', 'Akun tersimpan!');
        }

        return back()->with('failed', 'Akun gagal tersimpan!');        
    }
}
