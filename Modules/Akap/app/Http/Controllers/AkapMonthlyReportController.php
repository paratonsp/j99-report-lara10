<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkapMonthly;
use Illuminate\Support\Number;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use DateTime;
use DateInterval;
use DatePeriod;
use Helper;
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
        
        $tickSupport = $this->ticketingSupportChart($reportData);
        $reportData['ticketing_support_bar'] = $tickSupport['bar_chart'];
        $reportData['ticketing_support_pie_chart'] = $tickSupport['pie_chart'];

        return $reportData;
    }

    public function ticketingSupportChart($reportData)
    {
        $akap = AkapMonthly::getTicketingSupport($reportData);

        $onlineLabel = "Online";
        $onlineValue = 0;

        $agenLabel = "Agen";
        $agenValue = 0;

        $kpLabel = "KP";
        $kpValue = 0;

        $listAgen = array('ybc@gmail.com', 'no-reply@traveloka.com');
        $redbusAgen = 'ybc@gmail.com';
        $redbusLabel = 'RedBus';
        $redbusValue = 0;

        $travelokaAgen = 'no-reply@traveloka.com';
        $travelokaLabel = 'Traveloka';
        $travelokaValue = 0;



        foreach ($akap as $value) {
            if (in_array($value->booker, $listAgen)) {
                if ($value->booker == $redbusAgen) {
                    $redbusValue = $redbusValue + $value->passengger;
                }
                if ($value->booker == $travelokaAgen) {
                    $travelokaValue = $travelokaValue + $value->passengger;
                }
            } else {
                if (strstr(strtolower($value->booker), 'kantorperwakilan')) {
                    $kpValue = $kpValue + $value->passengger;
                } else {
                    $onlineValue = $onlineValue + $value->passengger;
                }
            }
        }

        $label = array($onlineLabel, $redbusLabel, $travelokaLabel, $kpLabel);
        $value = array($onlineValue, $redbusValue, $travelokaValue, $kpValue);
        $color = array(generateColor(0), generateColor(2), generateColor(4), generateColor(6));
        $data['bar_chart'] = Chartjs::build()
            ->name("TicketSupport")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 200])
            ->labels($label)
            ->datasets([
                [
                    "data" => $value,
                    'backgroundColor' => $color,
                    'stack' => 'Stack 0',

                ]
            ])->options([
                'plugins' => [
                    'legend' => false
                ]
            ]);
            
        $totalValue = $onlineValue + $redbusValue + $travelokaValue + $kpValue;
        $percentageValue = array();

        foreach ($value as $val) {
            $percentage = 0;
            $percentage = ($val * 100 / $totalValue);
            $percentage = number_format($percentage, 2, '.', '');
            array_push($percentageValue, $percentage);
        }

        $label = array($onlineLabel.": {$percentageValue[0]}%", $redbusLabel.": {$percentageValue[1]}%", $travelokaLabel.": {$percentageValue[2]}%", $kpLabel.": {$percentageValue[3]}%");

        $data['pie_chart'] = Chartjs::build()
        ->name("TicketingSupportPieChart")
        ->type("pie")
        ->size(["width" => 400, "height" => 400])
        ->labels($label)
        ->datasets([
            [
                "label" => "Penumpang",
                "data" => $value,
                'backgroundColor' => $color,
            ]
        ]);

        return $data;
    }

    public function getTicketData(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        if (env('AKAP_MONTHLY_REPORT_CACHE_ENABLED', false)) {
            $cacheKey = "akap_ticket_datax_{$year}_{$month}_{$trip}";

            $data = Cache::remember($cacheKey, 60 * 60, function () use ($month, $year) {
                return [
                    'getTicket' => AkapMonthly::getMonthlyTickets($month, $year),
                    'getBuy' => AkapMonthly::getMonthlyTickets($month, $year, true),
                    'getTicketPrevMonth' => AkapMonthly::getMonthlyTickets($prevDate->month, $prevDate->year),
                ];
            });
        } else {
            return [
                'getTicket' => AkapMonthly::getMonthlyTickets($month, $year),
                'getBuy' => AkapMonthly::getMonthlyTickets($month, $year, true),
                'getTicketPrevMonth' => AkapMonthly::getMonthlyTickets($prevDate->month, $prevDate->year),
            ];
        }

        return response()->json($data);
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