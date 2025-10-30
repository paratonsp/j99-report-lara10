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
        return $query->pluck('id');
    }

    public function scopeGetTripGroup($query, $param)
    {
        $query = DB::table('trip');
        if (isset($param['trip_route_group'])) {
            $query = $query->whereIn('route', $param['trip_route_group']);
        }
        return $query->pluck('trip_id');
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
    
    public function scopeGetSelling($query, $param)
    {
        $query = DB::table('tkt_booking_head as tbh')
            ->join('tkt_booking as tb', 'tbh.booking_code', '=', 'tb.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        $query->whereMonth('tb.date', $param['month'])
            ->whereYear('tb.date', $param['year']);

        return $query->sum(DB::raw("
            CASE 
            WHEN tbh.total_price = 0 THEN 0
            ELSE (tbh.total_price / tbh.total_seat)
            END
        "));
    }

    public function scopeGetIncome($query, $param)
    {
        $query = DB::table('tkt_booking_head as tbh')
            ->join('tkt_booking as tb', 'tbh.booking_code', '=', 'tb.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        $query->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year']);

        return $query->sum(DB::raw("
            CASE 
            WHEN tbh.total_price = 0 THEN 0
            ELSE (tbh.total_price / tbh.total_seat)
            END
        "));
    }

    public function scopeGetDailyPassengerCounts($query, $param)
    {
        $query = DB::table('tkt_booking as tb')
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0)
            ->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year']);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        return $query->groupBy(DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('DAY(tb.booking_date) as date'),
                DB::raw('COUNT(tpp.id) AS seat')
            )
            ->pluck('seat', 'date');
    }

    public function scopeGetDailyPassengerCountsByRoute($query, $param, $routes = [])
    {
        $query = DB::table('tkt_booking as tb')
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0)
            ->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year']);

        if (!empty($routes)) {
            $query->whereIn('tb.trip_route_id', $routes);
        }

        if (empty($param['trip_group'])) {
            // do nothing
        } else {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        return $query->groupBy('tb.trip_route_id', DB::raw('DAY(tb.booking_date)'))
            ->select(
                DB::raw('DAY(tb.booking_date) as date'),
                'tb.trip_route_id',
                DB::raw('COUNT(tpp.id) AS seat')
            )
            ->get();
    }

    public function scopeGetMonthlyIncomeAndSeats($query, $param)
    {
        $query = DB::table('tkt_booking as tb')
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0)
            ->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year']);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        return $query->select(
                DB::raw('SUM(tbh.total_price / tbh.total_seat) AS price'),
                DB::raw('COUNT(tpp.id) AS seat')
            )
            ->first();
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
                'tr.trip_title as trip',
                'tras.fleet_registration_id',
                'tras.status',
                'tras.id as tras_id',
                'tras.assign_time',
                'fr.reg_no as bus',
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
                'tr.trip_title as trip',
                'tras.fleet_registration_id',
                'tras.status',
                'tras.id as tras_id',
                'tras.assign_time',
                'fr.reg_no as bus',
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

    public function scopeGetSeatAndClassBookingData($query, $param)
    {
        $query = DB::table('tkt_booking as tb')
            ->join('tkt_booking_head as tbh', 'tb.booking_code', '=', 'tbh.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->leftJoin('fleet_type as ft', 'tb.fleet_type', '=', 'ft.id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0)
            ->whereMonth('tb.booking_date', $param['month'])
            ->whereYear('tb.booking_date', $param['year']);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }
        
        if (!empty($param['trip_assign_group'])) {
            $query->whereIn('tb.tras_id', $param['trip_assign_group']);
        }

        return $query->select('tb.trip_route_id', 'ft.type', 'tb.pickup_trip_location', 'tb.drop_trip_location', DB::raw('DAY(tb.booking_date) as date'))->get();
    }

    public function scopeGetBookByBus($query, $param)
    {
        $bindings = [
            $param['month'],
            $param['year'],
        ];

        $sql = "
            SELECT 
                bus.name AS name,
                mn.uuid AS uuid,
                x.date AS date,
                x.tras_id AS tras_id,
                SUM(x.total_seat) AS passengger
            FROM (
                SELECT 
                    tb.tras_id AS tras_id,
                    DATE(tb.booking_date) AS date,
                    COUNT(tps.id) AS total_seat
                FROM tkt_booking tb
                JOIN tkt_booking_head tbh 
                    ON tb.booking_code = tbh.booking_code
                JOIN tkt_passenger_pcs tps 
                    ON tb.id_no = tps.booking_id
                WHERE MONTH(tb.booking_date) = ?
                AND YEAR(tb.booking_date) = ?
                AND tbh.payment_status = 1
                AND tps.cancel = 0
        ";

        // filter trip_assign_group kalau ada
        if (!empty($param['trip_assign_group'])) {
            $placeholders = implode(',', array_fill(0, count($param['trip_assign_group']), '?'));
            $sql .= " AND tb.tras_id IN ($placeholders)";
            $bindings = array_merge($bindings, $param['trip_assign_group']);
        }

        $sql .= "
                GROUP BY tb.tras_id, DATE(tb.booking_date)
            ) x
            JOIN manifest mn 
                ON mn.trip_assign = x.tras_id 
            AND mn.trip_date = DATE(x.date)
            JOIN ops_roadwarrant rw 
                ON rw.uuid = mn.roadwarrant_uuid
            JOIN v2_bus bus 
                ON rw.bus_uuid = bus.uuid
            GROUP BY bus.name, mn.uuid, x.date, x.tras_id
        ";

        return DB::select($sql, $bindings);
    }

    public function scopeGetBookByTripAssign($query, $param)
    {
        $bindings = [
            $param['month'],
            $param['year'],
        ];

        $sql = "
            SELECT 
                tb.tras_id,
                DAY(tb.booking_date) AS date,
                COUNT(tps.id) AS seat
            FROM tkt_booking tb
            JOIN tkt_booking_head tbh 
                ON tb.booking_code = tbh.booking_code
            JOIN tkt_passenger_pcs tps 
                ON tb.id_no = tps.booking_id
            WHERE MONTH(tb.booking_date) = ?
            AND YEAR(tb.booking_date) = ?
            AND tbh.payment_status = 1
            AND tps.cancel = 0
        ";

        if (!empty($param['trip_assign_group'])) {
            $placeholders = implode(',', array_fill(0, count($param['trip_assign_group']), '?'));
            $sql .= " AND tb.tras_id IN ($placeholders)";
            $bindings = array_merge($bindings, $param['trip_assign_group']);
        }

        $sql .= "
            GROUP BY tb.tras_id, DAY(tb.booking_date)
        ";

        return DB::select($sql, $bindings);
    }

    public function scopeGetDailySelling($query, $startDate, $endDate)
    {
        $betweenDate = [$startDate, $endDate];
        $query = DB::table('tkt_booking_head as tbh')
            ->join('tkt_booking as tb', 'tbh.booking_code', '=', 'tb.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        $query = $query->whereBetween('tb.date', $betweenDate);

        return $query->selectRaw("
                DATE(tb.booking_date) as booking_date,
                tb.trip_route_id,
                SUM(
                    CASE 
                        WHEN tbh.total_price = 0 THEN 0
                        ELSE (tbh.total_price / tbh.total_seat)
                    END
                ) as total_price
            ")
            ->groupBy(DB::raw("DATE(tb.booking_date)"), "tb.trip_route_id")
            ->orderBy("booking_date", "ASC")
            ->get();
    }

    public function scopeGetDailyIncome($query, $startDate, $endDate)
    {
        $betweenDate = [$startDate, $endDate];
        $query = DB::table('tkt_booking_head as tbh')
            ->join('tkt_booking as tb', 'tbh.booking_code', '=', 'tb.booking_code')
            ->join('tkt_passenger_pcs as tpp', 'tb.id_no', '=', 'tpp.booking_id')
            ->where('tbh.payment_status', 1)
            ->where('tpp.cancel', 0);

        if (!empty($param['trip_group'])) {
            $query->whereIn('tb.trip_id_no', $param['trip_group']);
        }

        $query = $query->whereBetween('tb.booking_date', $betweenDate);

        return $query->selectRaw("
                DATE(tb.booking_date) as booking_date,
                tb.trip_route_id,
                SUM(
                    CASE 
                        WHEN tbh.total_price = 0 THEN 0
                        ELSE (tbh.total_price / tbh.total_seat)
                    END
                ) as total_price
            ")
            ->groupBy(DB::raw("DATE(tb.booking_date)"), "tb.trip_route_id")
            ->orderBy("booking_date", "ASC")
            ->get();
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