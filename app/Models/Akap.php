<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Akap extends Model
{

    public function scopeGetTripRouteGroup($query, $id = null)
    {
        $query = DB::table('trip_route_group');
        if ($id != null) {
            $query = $query->where('id', $id);
        }
        $query = $query->select('*')->get();

        return $query;
    }

    public function scopeGetTripAssignGroup($query, $param)
    {
        $query = DB::table('trip_assign');
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('trip', $param['trip_group']);
        }
        $query = $query->select('id')->get();

        return $query;
    }

    public function scopeGetTripGroup($query, $param)
    {
        $query = DB::table('trip');
        if (isset($param['trip_route_group'])) {
            $query = $query->whereIn('route', $param['trip_route_group']);
        }
        $query = $query->select('trip_id')->get();

        return $query;
    }

    public function scopeGetTripAssignOpen($query, $param)
    {
        $query = DB::table('trip_assign_temporary');
        if (isset($param['assign_id'])) {
            $query = $query->whereIn('assign_id', $param['assign_id']);
        }
        $query = $query->whereMonth('date', $param['month'])
            ->select('*')->get();

        return $query;
    }

    public function scopeGetTripAssignClose($query, $param)
    {
        $query = DB::table('trip_assign_dayoff');
        if (isset($param['assign_id'])) {
            $query = $query->whereIn('assign_id', $param['assign_id']);
        }
        $query = $query->whereMonth('date', $param['month'])
            ->select('*')->get();

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
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_id_no'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_id_no']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month']);
        $query = $query->whereYear('tb.booking_date', $param['year']);
        $query = $query->sum('price');

        return $query;
    }

    public function scopeGetDailyPassengger($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_id_no'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_id_no']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->where('tb.tkt_refund_id', NULL)
            ->groupBy(DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('DAY(tb.booking_date) as date, SUM(tb.total_seat) AS seat')
            )
            ->pluck('seat', 'date');

        return $query;
    }

    public function scopeGetTicketingSupport($query, $param)
    {
        // $query = DB::table('tkt_booking as tb');
        // if (isset($param['trip_id_no'])) {
        //     $query = $query->whereIn('tb.trip_id_no', $param['trip_id_no']);
        // }
        // $query = $query->whereMonth('tb.booking_date', $param['month']);
        // $query = $query->whereYear('tb.booking_date', $param['year']);
        // $query = $query->join('tkt_booking_head AS tbh', 'tbh.booking_code', '=', 'tb.booking_code');
        // $query = $query->get();

        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_id_no'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_id_no']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->where('tb.tkt_refund_id', NULL)
            ->join('tkt_booking_head AS tbh', 'tbh.booking_code', '=', 'tb.booking_code')
            ->leftJoin('user AS us', 'us.id', '=', 'tbh.agent')
            ->groupBy('tbh.agent')
            ->select(
                DB::raw('IFNULL(us.firstname, "Online") as name, SUM(tb.total_seat) AS passengger')
            )
            ->get();

        return $query;
    }
}
