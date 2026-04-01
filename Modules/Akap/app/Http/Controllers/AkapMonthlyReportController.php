<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkapMonthly;
use Illuminate\Support\Number;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AkapMonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');
        $trip = $request->input('trip');

        // if (env('AKAP_MONTHLY_REPORT_CACHE_ENABLED', false)) {
        //     $cacheKey = "akapmonthly11_{$year}_{$month}_{$trip}";

        //     $data = Cache::remember($cacheKey, 60 * 60, function () use ($request, $month, $year, $trip) {
        //         return $this->getReportData($request, $month, $year, $trip);
        //     });
        // } else {
            $data = $this->getReportData($request, $month, $year, $trip);
        // }

        // echo json_encode($data);
        // return;

        $data['title'] = 'REPORT AKAP BULANAN';
        return view('akap::monthlyreport', $data);
    }

    public function getReportData(Request $request, $month, $year, $trip)
    {
        $trip_route_grouped = null;
        $trip_route_group = null;
        $trip_group = null;
        $trip_assign_group = null;
        $total_days = Carbon::now()->month($month)->daysInMonth;

        $trip_route_grouped = $routeGroupResult = AkapMonthly::getTripRouteGroup();

        if ($request->has('trip')) {
            $trip_route_group = $trip_route_grouped = AkapMonthly::getTripRouteGroup($trip);
            if (isset($trip_route_group)) {
                $temp_route = array();
                $rx = explode(",", $trip_route_group[0]->route_x);
                foreach ($rx as $rxv) {
                    array_push($temp_route, $rxv);
                }
                $ry = explode(",", $trip_route_group[0]->route_y);
                foreach ($ry as $ryv) {
                    array_push($temp_route, $ryv);
                }
                $trip_route_grouped[0]->name = $trip_route_group[0]->name_x;
                $trip_route_grouped[0]->route = $temp_route;
                $trip_route_group = $temp_route;

            }
        } else {
            foreach ($trip_route_grouped as $value) {
                $temp_route = array();
                $rx = explode(",", $value->route_x);
                foreach ($rx as $rxv) {
                    array_push($temp_route, $rxv);
                }
                $ry = explode(",", $value->route_y);
                foreach ($ry as $ryv) {
                    array_push($temp_route, $ryv);
                }
                $value->name = $value->name_x;
                $value->route = $temp_route;

            }
        }

        //reportDataETER
        $reportData = [
            'trip_route_grouped' => $trip_route_grouped,
            'trip_route_group' => $trip_route_group,
            'trip_group' => $trip_group,
            'trip_assign_group' => $trip_assign_group,
            'total_days' => $total_days,
            'month' => $month,
            'year' => $year,
        ];


        $target = AkapMonthly::getTarget($reportData);
        if ($target->isEmpty()) {
            $target = "-";
        } else {
            $target = $target[0]->target;
        }
        
        $reportData['trip_group'] = AkapMonthly::getTripGroup($reportData)->toArray();
        $reportData['trip_assign_group'] = AkapMonthly::getTripAssignGroup($reportData)->toArray();
        $reportData['class_temp_off'] = AkapMonthly::getTemporaryOff($reportData);
        $reportData['class_temp_on'] = AkapMonthly::getTemporaryOn($reportData);
        $reportData['class_info'] = $this->classInfo($reportData);
        $reportData['target'] = $target;
        
        return $reportData;
    }

    private function resolveTripRouteIds($trip): array
    {
        if (!$trip) return [];
        $tripRouteGroup = AkapMonthly::getTripRouteGroup($trip);
        if ($tripRouteGroup->isEmpty()) return [];
        $rx = array_filter(array_map('trim', explode(',', $tripRouteGroup[0]->route_x)));
        $ry = array_filter(array_map('trim', explode(',', $tripRouteGroup[0]->route_y)));
        return array_values(array_merge($rx, $ry));
    }

    public function getTicket(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $tripRouteIds = $this->resolveTripRouteIds($request->input('trip'));

        return response()->json(AkapMonthly::getMonthlyTickets($month, $year, false, $tripRouteIds));
    }

    public function getBuy(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $tripRouteIds = $this->resolveTripRouteIds($request->input('trip'));

        return response()->json(AkapMonthly::getMonthlyTickets($month, $year, true, $tripRouteIds));
    }

    public function getTicketPrevMonth(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $tripRouteIds = $this->resolveTripRouteIds($request->input('trip'));
        $prevDate = Carbon::create($year, $month, 1)->subMonth();

        return response()->json(AkapMonthly::getMonthlyTickets($prevDate->month, $prevDate->year, false, $tripRouteIds));
    }

    public function classInfo($reportData)
    {
        $classInfo = AkapMonthly::getAkapClassInfoList($reportData);

        $totalDays = Carbon::now()->month($reportData['month'])->daysInMonth;

        foreach ($classInfo as $value) {
            $value->days_active = $totalDays;
        }

        $tempOnClassInfo = AkapMonthly::getTemporaryOnClassInfo($reportData);
        foreach ($tempOnClassInfo as $value) {
            $dateFrom = Carbon::parse($value->date);
            $dateTo = Carbon::parse($value->date_finish);
            $value->days_active = $dateFrom->diffInDays($dateTo) + 1;
            $classInfo->push($value);
        }

        return $classInfo;
    }
}