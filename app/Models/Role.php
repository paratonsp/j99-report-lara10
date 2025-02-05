<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table = 'v2_role';
    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
    ];

    public function scopeGetRole($query)
    {
        $query = DB::table("v2_role")
            ->select('uuid','title','slug','description')
            ->where('status', 1)
            ->orderBy('created_at')
            ->get();

        return $query;
    }

    public function scopeGetRoleAccess($query, $datas)
    {
        $role_id = isset($datas['role_id']) ? $datas['role_id'] : '';

        $query = DB::table("v2_role_permission AS roleperm")
            ->select(DB::raw("CONCAT(perm.slug,' ',perm.access) as slugaccess"))
            ->join("v2_permission AS perm", "perm.id", "=", "roleperm.permission_id")
            ->where('roleperm.role_id', $role_id)
            ->where('perm.access', '!=' ,'index')
            ->where('perm.status', 1)
            ->orderBy('perm.slug')
            ->get();

        return collect($query)->pluck('slugaccess')->toArray();
    }

    public function scopeGetRoleInfo($query, $role_uuid)
    {
        $query = DB::table("v2_role")
            ->select('id','uuid','title','slug','description')
            ->where('uuid', $role_uuid)
            ->orderBy('created_at')
            ->first();

        return $query;
    }

    public function scopeGetAccess($query)
    {
        $query = DB::table("v2_access_name AS acname")
            ->select('name')
            ->orderBy('id')
            ->get();

        return $query;
    }

    public function scopeGetPermission($query, $slug, $access)
    {
        $query = DB::table("v2_permission")
            ->select('id AS permid')
            ->where('slug', $slug)
            ->where('access', $access)
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        return $query;
    }

    public function scopeGetRolePermission($query, $id, $role_id)
    {
        $query = DB::table("v2_role_permission as roleperm")
            ->where('roleperm.permission_id', $id)
            ->where('roleperm.role_id', $role_id)
            ->exists();

        return $query;
    }

    public function scopeGetRoleId($query, $role_uuid)
    {
        $query = DB::table("v2_role")
            ->select('id as roleid')
            ->where('uuid', $role_uuid)
            ->first();

        return $query;
    }

    public function scopeSaveRolePermission($query, $data)
    {
        $query = DB::table("v2_role_permission")->insert($data);

        return $query;
    }

    public function scopeDeleteRolePermission($query, $role_id)
    {
        $query = DB::table("v2_role_permission")
            ->where('role_id', $role_id)
            ->delete();

        return $query;
    }
}
