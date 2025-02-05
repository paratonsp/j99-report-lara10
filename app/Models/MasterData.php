<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterData extends Model
{
    public function scopeGetMasterBusList($query)
    {
        $query = DB::table("v2_bus AS bus")
            ->select('bus.uuid','bus.name','bus.registration_number','bus.brand','bus.model','bus.status','class.name AS class')
            ->join("v2_class AS class", "class.uuid", "=", "bus.class_uuid")
            ->orderBy('bus.created_at')
            ->get();

        return $query;
    }

    public function scopeGetMasterClassList($query)
    {
        $query = DB::table("v2_class")
            ->select('id','uuid','name','seat')
            ->orderBy('id')
            ->get();

        return $query;
    }

    public function scopeGetMasterFacilities($query)
    {
        $query = DB::table("v2_facilities")
            ->select('id','name')
            ->orderBy('id')
            ->get();

        return $query;
    }

    public function scopeSaveMasterBus($query, $data)
    {
        $query = DB::table("v2_bus")->insert($data);

        return $query;
    }

    public function scopeSaveMasterClass($query, $data)
    {
        $query = DB::table("v2_class")->insert($data);

        return $query;
    }

    public function scopeSaveClassFacilities($query, $data)
    {
        $query = DB::table("v2_class_facilities")->insert($data);

        return $query;
    }

    public function scopesaveFacility($query, $data)
    {
        $query = DB::table("v2_facilities")->insert($data);

        return $query;
    }
}
