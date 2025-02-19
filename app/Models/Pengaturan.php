<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Pengaturan extends Model
{
    public function scopeGetTripRouteGroup($query)
    {
        $query = DB::table('trip_route_group');
        $query = $query->select('*')->get();

        return $query;
    }

    public function scopeGetAllTripRoute($query)
    {
        $query = DB::table('trip_route');
        $query = $query->select('*')->get();

        return $query;
    }

    public function scopeCreateTripRouteGroup($query, $data)
    {
        $query = DB::table("trip_route_group")->insert($data);

        return $query;
    }

    public function scopeUpdateTripRouteGroup($query, $data)
    {
        $query = DB::table("trip_route_group")
            ->where('id', $data['id'])
            ->update($data);

        return $query;
    }
}
