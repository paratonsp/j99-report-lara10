<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    protected $table = 'v2_menu';
    protected $fillable = [
        'title',
        'url',
        'module',
        'parent_id',
        'order',
        'icon',
        'slug',
        'status',
    ];

    public function scopeGetUserRoleInfo($query, $datas)
    {
        $email = isset($datas['email']) ? $datas['email'] : '';

        $query = DB::table("users")
            ->select('users.role_uuid','role.title','role.id as role_id')
            ->join("v2_role AS role", "role.uuid", "=", "users.role_uuid")
            ->where('users.email', $email)
            ->first();

        return $query;
    }

    public function scopeGetMenuParent($query)
    {
        $query = DB::table("v2_menu As menu")
            ->select('menu.id','menu.title','menu.slug','menu.url','menu.icon')
            ->where('menu.parent_id', NULL)
            ->where('menu.status', 1)
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeGetMenu($query)
    {
        $query = DB::table("v2_menu As menu")
            ->select('menu.title','menu.slug','menu.url','menu.icon','menu2.title as parent_title')
            ->leftJoin("v2_menu AS menu2", "menu2.id", "=", "menu.parent_id")
            ->where('menu.status', 1)
            ->orderBy('menu2.title')
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeGetMenuWithRole($query, $datas)
    {
        $role_id = isset($datas['role_id']) ? $datas['role_id'] : '';

        $query = DB::table("v2_menu AS menu")
            ->select('menu.id', 'menu.title', 'menu.url', 'menu.icon')
            ->join("v2_permission AS perm", "perm.slug", "=", "menu.slug")
            ->join("v2_role_permission AS roleperm", "roleperm.permission_id", "=", "perm.id")
            ->where('roleperm.role_id', $role_id)
            ->where('perm.access', 'index')
            ->where('menu.parent_id', NULL)
            ->where('perm.status', 1)
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeGetChildMenu($query, $datas)
    {
        $parent_id = isset($datas['parent_id']) ? $datas['parent_id'] : '';
        $role_id = isset($datas['role_id']) ? $datas['role_id'] : '';

        $query = DB::table("v2_menu AS menu")
            ->select('menu.id', 'menu.title', 'menu.url', 'menu.icon')
            ->join("v2_permission AS perm", "perm.slug", "=", "menu.slug")
            ->join("v2_role_permission AS roleperm", "roleperm.permission_id", "=", "perm.id")
            ->where('roleperm.role_id', $role_id)
            ->where('perm.access', 'show')
            ->where('menu.parent_id', $parent_id)
            ->where('perm.status', 1)
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeSavePermission($query, $data)
    {
        $query = DB::table("v2_permission")->insert($data);

        return $query;
    }
}
