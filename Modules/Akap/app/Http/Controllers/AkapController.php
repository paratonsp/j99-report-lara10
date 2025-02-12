<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akap;
use Illuminate\Support\Number;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use DateTime;
use Helper;

class AkapController extends Controller
{
    public function index(Request $request)
    {
        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');
        $trip_route_group = null;
        $trip_group = null;
        $trip_assign_group = null;
        $trip_assign_open = null;
        $trip_assign_close = null;

        if ($request->has('trip')) {
            $trip = $request->input('trip');
            $trip_route_group = Akap::getTripRouteGroup($trip);
            if (isset($trip_route_group)) {
                $temp_route = array();
                array_push($temp_route, $trip_route_group[0]->route_x);
                array_push($temp_route, $trip_route_group[0]->route_y);
                $trip_route_group = $temp_route;
            }
        }

        $paramTripGroup = [
            'trip_route_group' => $trip_route_group,
        ];
        $trip_group = Akap::getTripGroup($paramTripGroup)->toArray();
        $trip_group = join(',', array_column($trip_group, 'trip_id'));
        $trip_group = explode(",", $trip_group);

        $paramTripAssignGroup = [
            'trip_group' => $trip_group,
        ];
        $trip_assign_group = Akap::getTripAssignGroup($paramTripAssignGroup)->toArray();
        $trip_assign_group = join(',', array_column($trip_assign_group, 'id'));
        $trip_assign_group = explode(",", $trip_assign_group);

        $paramIncome = [
            'month' => $month,
            'year' => $year,
            'trip_id_no' => $trip_group,
        ];
        $data['income'] = Number::currency(Akap::getIncome($paramIncome), 'IDR');

        $daily_passengger = Akap::getDailyPassengger($paramIncome);
        $keys = $daily_passengger->keys()->toArray();
        $values = $daily_passengger->values()->toArray();

        $data['chartDailyPassengger'] = Chartjs::build()
            ->name("DailyPassengger")
            ->type("bar")
            ->size(["width" => 400, "height" => 200])
            ->labels($keys)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $values,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
                ],
                [
                    "label" => "Penumpang 2",
                    "data" => $values,
                    'backgroundColor' => generateColor(1),
                    'stack' => 'Stack 0',
                ]
            ]);

        $data['route_group'] = Akap::getTripRouteGroup();
        $data['title'] = 'REPORT AKAP';

        $paramTripAssign = [
            'assign_id' => $trip_assign_group,
            'month' => $month,
        ];
        $trip_assign_open = Akap::getTripAssignOpen($paramTripAssign);
        foreach ($trip_assign_open as $key => $item) {
            $date = DateTime::createFromFormat("Y-m-d", $item->date)->format("d");
            $date_finish = DateTime::createFromFormat("Y-m-d", $item->date_finish)->format("d");
            $date_count = ($date_finish - $date) + 1;
            $trip_assign_open[$key]->date_count = $date_count;
        }

        $trip_assign_close = Akap::getTripAssignClose($paramTripAssign);
        foreach ($trip_assign_close as $key => $item) {
            $date = DateTime::createFromFormat("Y-m-d", $item->date)->format("d");
            $date_finish = DateTime::createFromFormat("Y-m-d", $item->date_finish)->format("d");
            $date_count = ($date_finish - $date) + 1;
            $trip_assign_close[$key]->date_count = $date_count;
        }


        $paramX = [
            'month' => $month,
            'year' => $year,
            'trip_id_no' => $trip_group,
        ];
        $x = Akap::getTicketingSupport($paramX);

        $ticket_support_label = $x->pluck('name')->toArray();
        $ticket_support_value = $x->pluck('passengger')->toArray();
        $data['chartTicketSupport'] = Chartjs::build()
            ->name("TicketSupport")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 200])
            ->labels($ticket_support_label)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $ticket_support_value,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',

                ]
            ])->options([
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Monthly User Registrations'
                    ]
                ]
            ]);


        $data['trip_assign_open'] = $trip_assign_open;
        $data['trip_assign_close'] = $trip_assign_close;

        return view('akap::index', $data);
    }
}
