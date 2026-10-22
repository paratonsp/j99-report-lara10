<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkapMonthly;
use Carbon\Carbon;

class AkapDailyReportController extends Controller
{
    public function index(Request $request)
    {
        $dateStart = $request->input('dateStart', date('Y-m-d'));
        $dateEnd   = $request->input('dateEnd',   date('Y-m-d'));
        $trip      = $request->input('trip');

        $trip_route_grouped = AkapMonthly::getTripRouteGroup();
        foreach ($trip_route_grouped as $value) {
            $temp_route = [];
            foreach (explode(',', $value->route_x) as $r) { $temp_route[] = trim($r); }
            foreach (explode(',', $value->route_y) as $r) { $temp_route[] = trim($r); }
            $value->name  = $value->name_x;
            $value->route = $temp_route;
        }

        $tripRouteIds = $this->resolveTripRouteIds($trip);

        return view('akap::dailyreport', [
            'title'              => 'REPORT AKAP HARIAN',
            'trip_route_grouped' => $trip_route_grouped,
            'dateStart'          => $dateStart,
            'dateEnd'            => $dateEnd,
            'trip'               => $trip,
            'class_temp_off'     => AkapMonthly::getDailyTemporaryOff($dateStart, $dateEnd, $tripRouteIds),
            'class_temp_on'      => AkapMonthly::getDailyTemporaryOn($dateStart, $dateEnd, $tripRouteIds),
        ]);
    }

    private function resolveTripRouteIds($trip): array
    {
        if (!$trip) return [];
        $group = AkapMonthly::getTripRouteGroup($trip);
        if ($group->isEmpty()) return [];
        $rx = array_filter(array_map('trim', explode(',', $group[0]->route_x)));
        $ry = array_filter(array_map('trim', explode(',', $group[0]->route_y)));
        return array_values(array_merge($rx, $ry));
    }

    public function getTicket(Request $request)
    {
        $dateStart    = $request->input('dateStart', date('Y-m-d'));
        $dateEnd      = $request->input('dateEnd',   date('Y-m-d'));
        $tripRouteIds = $this->resolveTripRouteIds($request->input('trip'));

        return response()->json(AkapMonthly::getDailyTickets($dateStart, $dateEnd, false, $tripRouteIds));
    }

    public function getBuy(Request $request)
    {
        $dateStart    = $request->input('dateStart', date('Y-m-d'));
        $dateEnd      = $request->input('dateEnd',   date('Y-m-d'));
        $tripRouteIds = $this->resolveTripRouteIds($request->input('trip'));

        return response()->json(AkapMonthly::getDailyTickets($dateStart, $dateEnd, true, $tripRouteIds));
    }
}