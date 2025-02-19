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
        if (isset($param['trip_assign_group'])) {
            $query = $query->whereIn('assign_id', $param['trip_assign_group']);
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
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
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

    public function scopeGetAkapClassInfoList($query, $param)
    {

        $query = DB::table('trip as tr');
        if (isset($param['trip_route_group'])) {
            $query = $query->whereIn('tr.route', $param['trip_route_group']);
        }
        $query = $query
            ->select(
                'tr.route as trip_route_id',
                'tras.fleet_registration_id',
                'tras.status',
                'tras.id as tras_id',
                'tras.assign_time',
                'frt.registration',
                'ft.id as fleet_type',
                'ft.type',
                'ft.total_seat',
            )
            ->join('trip_assign AS tras', 'tr.trip_id', '=', 'tras.trip')
            ->join('fleet_registration AS fr', 'tras.fleet_registration_id', '=', 'fr.id')
            ->join('fleet_registration_type AS frt', 'fr.reg_no', '=', 'frt.registration')
            ->join('fleet_type AS ft', 'frt.type', '=', 'ft.id')
            ->get();


        return $query;
    }

    public function scopeGetTemporaryOff($query, $param)
    {
        $query = DB::table('trip as tr');
        if (isset($param['trip_route_group'])) {
            $query = $query->whereIn('tr.route', $param['trip_route_group']);
        }
        $query = $query->select(
            'tras.fleet_registration_id',
            'tad.date',
            'tad.date_finish',
        )
            ->join('trip_assign AS tras', 'tr.trip_id', '=', 'tras.trip')
            ->join('trip_assign_dayoff AS tad', 'tras.id', '=', 'tad.assign_id')
            ->where('tras.status', '1')
            ->whereMonth('tad.date', $param['month'])
            ->whereYear('tad.date', $param['year'])
            ->whereMonth('tad.date_finish', $param['month'])
            ->whereYear('tad.date_finish', $param['year'])
            ->get();

        return $query;
    }

    public function scopeGetTemporaryOn($query, $param)
    {
        $query = DB::table('trip as tr');

        if (isset($param['trip_route_group'])) {
            $query = $query->whereIn('tr.route', $param['trip_route_group']);
        }
        $query = $query->select(
            'tras.fleet_registration_id',
            'tat.date',
            'tat.date_finish',
        )
            ->join('trip_assign AS tras', 'tr.trip_id', '=', 'tras.trip')
            ->join('trip_assign_temporary AS tat', 'tras.id', '=', 'tat.assign_id')
            ->where('tras.status', '0')
            ->whereMonth('tat.date', $param['month'])
            ->whereYear('tat.date', $param['year'])
            ->whereMonth('tat.date_finish', $param['month'])
            ->whereYear('tat.date_finish', $param['year'])
            ->get();

        return $query;
    }

    public function scopeGetBookSeat($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        $query = $query->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code');
        $query = $query->where('tbh.payment_status', 1);
        $query = $query->whereMonth('tb.booking_date', $param['month']);
        $query = $query->whereYear('tb.booking_date', $param['year']);
        $query = $query->groupBy('tb.trip_route_id')
            ->select(
                DB::raw('tb.trip_route_id, SUM(tb.total_seat) as passengger')
            )
            ->get();

        return $query;
    }

    public function scopeGetBookByTripAssign($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_assign_group'])) {
            $query = $query->whereIn('tb.tras_id', $param['trip_assign_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->where('tb.tkt_refund_id', NULL)
            ->groupBy('tb.tras_id')
            ->groupBy(DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('tb.tras_id, DAY(tb.booking_date) as date, SUM(tb.total_seat) AS seat')
            )
            ->get();

        return $query;
    }

    public function scopeGetDailyIncome($query, $startDate, $endDate)
    {
        $betweenDate = [$startDate, $endDate];
        $query = DB::table('tkt_booking as tb');
        $query = $query->whereBetween('tb.booking_date', $betweenDate);
        $query = $query->groupBy('tb.trip_route_id');
        $query = $query->select(
            DB::raw('tb.trip_route_id, SUM(tb.price) AS price')
        );
        $query = $query->get();

        return $query;
    }
}
