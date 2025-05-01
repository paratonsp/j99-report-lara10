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

    public function scopeGetAllAkapTarget($query)
    {
        $query = DB::table('report_target_akap')
            ->select('*')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get();

        return $query;
    }

    public function scopeCheckAkapTarget($query, $data)
    {
        $query = DB::table("report_target_akap")
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->select('*')
            ->get();
        return $query;
    }

    public function scopeCreateAkapTarget($query, $data)
    {
        $query = DB::table("report_target_akap")->insert($data);
        return $query;
    }

    public function scopeUpdateAkapTarget($query, $data)
    {
        $query = DB::table("report_target_akap")
            ->where('id', $data['id'])
            ->update($data);

        return $query;
    }

    public function scopeDeleteAkapTarget($query, $data)
    {
        $query = DB::table("report_target_akap")
            ->where('id', $data['id'])
            ->delete();

        return $query;
    }
}
