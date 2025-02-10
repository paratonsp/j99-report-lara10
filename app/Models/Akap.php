<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Akap extends Model
{

    public function scopeGetTripRouteGroup($query)
    {
        $query = DB::table('trip_route as tr')
            ->select('tr.name', 'tr.start_point', 'tr.end_point')
            ->groupBy('tr.start_point')
            ->groupBy('tr.end_point')
            ->get();

        return $query;
    }

    public function scopeGetTripAssign($query, $param)
    {

        $query = DB::table('trip_route as tr')
            ->join('trip as trip', 'trip.route', '=', 'tr.id')
            ->join('trip_assign as ta', 'ta.trip', '=', 'trip.trip_id');

        if (isset($param['start_point']) && isset($param['end_point'])) {
            $query = $query
                ->where('tr.start_point', $param['start_point'])
                ->where('tr.end_point', $param['end_point']);
        }
        $query = $query->select('ta.id')->get();

        return $query;
    }

    public function scopeGetIncome($query, $param)
    {
        // dd($param['tras_id']);
        $query = DB::table('tkt_booking as tb');
        if (isset($param['tras_id'])) {
            $query = $query->whereIn('tb.tras_id', $param['tras_id']);
        }

        $query = $query->whereMonth('tb.booking_date', $param['month']);
        $query = $query->whereYear('tb.booking_date', $param['year']);
        $query = $query->sum('price');

        return $query;
    }
}
