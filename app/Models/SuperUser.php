<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SuperUser extends Model
{
    protected $table = 'report_menu';
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

    public function scopeGetMenuSU($query)
    {
        $query = DB::table("report_menu AS menu")
            ->select('menu.id', 'menu.title', 'menu.url', 'menu.icon')
            ->where('menu.parent_id', NULL)
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeGetChildMenuSU($query, $datas)
    {
        $parent_id = isset($datas['parent_id']) ? $datas['parent_id'] : '';

        $query = DB::table("report_menu AS menu")
            ->select('menu.id', 'menu.title', 'menu.url', 'menu.icon')
            ->where('menu.parent_id', $parent_id)
            ->orderBy('menu.order')
            ->get();

        return $query;
    }

    public function scopeGetRoleAccess($query)
    {
        $query = DB::table("v2_permission AS perm")
            ->select(DB::raw("CONCAT(perm.slug,' ',perm.access) as slugaccess"))
            ->where('perm.access', '!=', 'index')
            ->where('perm.status', 1)
            ->orderBy('perm.slug')
            ->get();

        return collect($query)->pluck('slugaccess')->toArray();
    }
}
