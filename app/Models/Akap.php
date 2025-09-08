<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Akap extends Model
{

    public function scopeGetTarget($query, $param)
    {
        $query = DB::table('report_target_akap');
        $query = $query->where('month', $param['month'])->where('year', $param['year']);;
        $query = $query->select('*')->get();

        return $query;
    }

    public function scopeGetTripRouteGroup($query, $id = null)
    {
        $query = DB::table('trip_route_group');
        if ($id != null) {
            $query = $query->where('id', $id);
        }
        $query = $query->select('*')->get();

        return $query;
    }
    public function scopeGetTripRouteGroupByName($query, $name)
    {
        $query = DB::table('trip_route_group');
        $query = $query->where('name_x', $name);
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
        $query = DB::table('trip_assign_temporary as tat');
        if (isset($param['trip_assign_group'])) {
            $query = $query->whereIn('tat.assign_id', $param['trip_assign_group']);
        }
        $query = $query
            ->join('trip_assign as tras', 'tras.id', '=', 'tat.assign_id')
            ->join('fleet_registration as fr', 'fr.id', '=', 'tras.fleet_registration_id')
            ->whereMonth('tat.date', $param['month'])
            ->whereYear('tat.date', $param['year'])
            ->select('tat.*', 'fr.reg_no')->get();

        return $query;
    }

    public function scopeGetTripAssignClose($query, $param)
    {
        $query = DB::table('trip_assign_dayoff as tad');
        if (isset($param['assign_id'])) {
            $query = $query->whereIn('assign_id', $param['assign_id']);
        }
        $query = $query
            ->join('trip_assign as tras', 'tras.id', '=', 'tad.assign_id')
            ->join('fleet_registration as fr', 'fr.id', '=', 'tras.fleet_registration_id')
            ->whereMonth('tad.date', $param['month'])
            ->whereYear('tad.date', $param['year'])
            ->select('tad.*', 'fr.reg_no')->get();

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
        $query = $query->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code');
        $query = $query->where('tbh.payment_status', 1);
        $query = $query->sum('price');

        return $query;
    }

    public function scopeGetDailyPassengger($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
            ->groupBy(DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('DAY(tb.booking_date) as date, SUM(tb.total_seat) AS seat')
            )
            ->pluck('seat', 'date');

        return $query;
    }

    public function scopeGetDailyPassenggerIncome($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->where('tb.tkt_refund_id', NULL)
            ->select(
                DB::raw('SUM(tb.total_seat) AS seat, SUM(tb.price) AS price')
            )
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
            ->get();

        return $query;
    }

    public function scopeGetTicketDeparturePointGroup($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->groupBy(DB::raw('tb.pickup_trip_location'))
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
            ->select(
                DB::raw('SUM(tb.total_seat) AS seat, tb.pickup_trip_location as point')
            )
            ->orderBy('seat', 'DESC')
            ->pluck('seat', 'point');

        return $query;
    }

    public function scopeGetTicketArrivalPointGroup($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->groupBy(DB::raw('tb.drop_trip_location'))
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
            ->select(
                DB::raw('SUM(tb.total_seat) AS seat, tb.drop_trip_location as point')
            )
            ->orderBy('seat', 'DESC')
            ->pluck('seat', 'point');

        return $query;
    }

    public function scopeGetDailyPassenggerByTrip($query, $param, $trip)
    {
        $query = DB::table('tkt_booking as tb');
        $query = $query->whereIn('tb.trip_route_id', $trip);
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
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
        if (isset($param['trip_group'])) {
            $query = $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        $query = $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->join('tkt_booking_head AS tbh', 'tbh.booking_code', '=', 'tb.booking_code')
            ->where('tbh.payment_status', 1)
            ->select(
                DB::raw('tbh.booker, tb.total_seat AS passengger')
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
            ->where('tras.status', 1)
            ->orderBy('tras.status', 'DESC')
            ->orderBy('tras.id', 'ASC')
            ->get();


        return $query;
    }

    public function scopeGetAkapClassInfoTable($query, $param)
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
            ->where('tras.status', 1)
            ->orderBy('tras.status', 'DESC')
            ->orderBy('tras.id', 'ASC')
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

    public function scopeGetBookByClass($query, $param)
    {
        $query = DB::table('tkt_booking as tb');
        if (isset($param['trip_assign_group'])) {
            $query = $query->whereIn('tb.tras_id', $param['trip_assign_group']);
        }
        $query = $query->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code');
        $query = $query->join('fleet_type as ft', 'tb.fleet_type', '=', 'ft.id');
        $query = $query->where('tbh.payment_status', 1);
        $query = $query->whereMonth('tb.booking_date', $param['month']);
        $query = $query->whereYear('tb.booking_date', $param['year']);
        $query = $query->groupBy('ft.type')
            ->select(
                DB::raw('ft.type, SUM(tb.total_seat) as passengger')
            )
            ->get();

        return $query;
    }

    public function scopeGetBookByBus($query, $param)
    {
        // Build the subquery
        $subQuery = DB::table('tkt_booking as tb')
            ->select(
                'tb.tras_id as tras_id',
                DB::raw('DATE(tb.booking_date) as date'),
                DB::raw('SUM(tb.total_seat) as total_seat'),
            )
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year'])
            ->where('tbh.payment_status', 1);

        if (isset($param['trip_assign_group'])) {
            $subQuery = $subQuery->whereIn('tb.tras_id', $param['trip_assign_group']);
        }

        $subQuery = $subQuery->groupBy('tb.tras_id', DB::raw('DATE(tb.booking_date)'));


        // Use the subquery as a derived table
        $query = DB::table(DB::raw("({$subQuery->toSql()}) as x"))
            ->mergeBindings($subQuery) // important to include bindings!
            ->join('manifest as mn', function ($join) {
                $join->on('mn.trip_assign', '=', 'x.tras_id')
                    ->whereRaw('mn.trip_date = DATE(x.date)');
            })
            // ->join('ops_roadwarrant as rw', 'rw.manifest_uuid', '=', 'mn.uuid')
            ->join('ops_roadwarrant as rw', 'rw.uuid', '=', 'mn.roadwarrant_uuid')
            ->join('v2_bus as bus', 'rw.bus_uuid', '=', 'bus.uuid')
            ->select(
                DB::raw('bus.name as name'),
                DB::raw('mn.uuid as uuid'),
                DB::raw('x.date as date'),
                DB::raw('x.tras_id as tras_id'),
                DB::raw('SUM(x.total_seat) as passengger')
            )
            ->groupBy('bus.name', 'mn.uuid')
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
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->where('tbh.payment_status', 1)
            ->groupBy('tb.tras_id')
            ->groupBy(DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('tb.tras_id, DAY(tb.booking_date) as date, id_no as idNo')
            )
            ->get();

        return $query;
    }

    public function scopeGetPassengers($query, $idNo)
    {
        $query = DB::table('tkt_passenger_pcs');
        $query = $query->where('booking_id', $idNo);
        $query = $query->where('cancel', 0);
        $query = $query->select('*')->get();

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
        $query = $query->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code');
        $query = $query->where('tbh.payment_status', 1);
        $query = $query->get();

        return $query;
    }

    public function scopeGetTripAssignDetail($query, $id)
    {
        $query = DB::table('trip_assign as tras');
        $query = $query->join('fleet_registration as fr', 'tras.fleet_registration_id', '=', 'fr.id');
        $query = $query->join('trip as tr', 'tras.trip', '=', 'tr.trip_id');
        $query = $query->where('tras.id', $id);
        $query = $query->select(
            'tr.trip_title as trip',
            'fr.reg_no as bus',
        );
        $query = $query->get();

        return $query;
    }
}
