<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akap;
use Illuminate\Support\Number;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use DateTime;
use DateInterval;
use DatePeriod;
use Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AkapMonthlyController extends Controller
{
    public function index(Request $request)
    {
        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');
        $trip = $request->input('trip');

        if (env('AKAP_MONTHLY_REPORT_CACHE_ENABLED', false)) {
            $cacheKey = "akap_monthly_report_{$year}_{$month}_{$trip}";

            $data = Cache::remember($cacheKey, 60 * 60, function () use ($request, $month, $year, $trip) {
                return $this->getReportData($request, $month, $year, $trip);
            });
        } else {
            $data = $this->getReportData($request, $month, $year, $trip);
        }

        return view('akap::monthly', $data);
    }

    public function getReportData(Request $request, $month, $year, $trip)
    {
        $trip_route_grouped = null;
        $trip_route_group = null;
        $trip_group = null;
        $trip_assign_group = null;
        $total_days = Carbon::now()->month($month)->daysInMonth;

        $trip_route_grouped = $routeGroupResult = Akap::getTripRouteGroup();

        if ($request->has('trip')) {
            $trip_route_group = $trip_route_grouped = Akap::getTripRouteGroup($trip);
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

        //PARAMETER
        $param = [
            'trip_route_grouped' => $trip_route_grouped,
            'trip_route_group' => $trip_route_group,
            'trip_group' => $trip_group,
            'trip_assign_group' => $trip_assign_group,
            'total_days' => $total_days,
            'month' => $month,
            'year' => $year,
        ];


        $target = Akap::getTarget($param);
        if ($target->isEmpty()) {
            $target = "Target belum disetting";
        } else {
            $target = Number::currency($target[0]->target, 'IDR');
        }

        $classInfo = $this->classInfo($param);

        $param['trip_group'] = Akap::getTripGroup($param)->toArray();
        $param['trip_assign_group'] = Akap::getTripAssignGroup($param)->toArray();

        $seatAndClassBookingData = Akap::getSeatAndClassBookingData($param);

        //CHART DATA
        $occRoute = $this->occupancyByRouteChart($param, $classInfo, $seatAndClassBookingData);
        $data['occupancy_by_route_bar'] = $occRoute['bar_chart'];
        $data['occupancy_by_route_doughnut'] = $occRoute['doughnut_chart'];

        $occBus = $this->occupancyByBusChart($param,$classInfo);
        $data['occupancy_by_bus_bar'] = $occBus['bar_chart'];
        $data['occupancy_by_bus_doughnut'] = $occBus['doughnut_chart'];

        $occClass = $this->occupancyByClassChart($param, $classInfo, $seatAndClassBookingData);
        $data['occupancy_by_class_bar'] = $occClass['bar_chart'];
        $data['occupancy_by_class_doughnut'] = $occClass['doughnut_chart'];

        $tickSupport = $this->ticketingSupportChart($param);
        $data['ticketing_support_bar'] = $tickSupport['bar_chart'];
        $data['ticketing_support_pie_chart'] = $tickSupport['pie_chart'];

        $data['daily_passengger'] = $this->dailyPassenggerChart($param, $seatAndClassBookingData);
        $data['total_keterisian_kursi'] = $this->totalKeterisianKursiChart($param, $classInfo, $seatAndClassBookingData);

        $perbBulanLalu = $this->perbandinganBulanLaluChart($param);
        $data['perbandingan_bulan_lalu_chart'] = $perbBulanLalu['chart'];
        $data['perbandingan_bulan_lalu_current_month'] = $perbBulanLalu['current_month'];
        $data['perbandingan_bulan_lalu_last_month'] = $perbBulanLalu['last_month'];

        $perbTitikNaik = $this->perbandinganTitikNaik($param, $seatAndClassBookingData);
        $data['perbandingan_titik_naik_departure_bar_chart'] = $perbTitikNaik['departure_bar_chart'];
        $data['perbandingan_titik_naik_arrival_bar_chart'] = $perbTitikNaik['arrival_bar_chart'];
        $data['perbandingan_titik_naik_departure_doughnut_chart'] = $perbTitikNaik['departure_doughnut_chart'];
        $data['perbandingan_titik_naik_arrival_doughnut_chart'] = $perbTitikNaik['arrival_doughnut_chart'];


        //DATA
        $data['income'] = $perbBulanLalu['current_month']['income'];
        $data['selling'] = Number::currency(Akap::getSelling($param), 'IDR');
        $data['target'] = $target;
        $data['route_group'] = $routeGroupResult;
        $data['title'] = 'REPORT AKAP BULANAN';

        $daftarAbsensi = $this->daftarAbsensiBus($param);
        $data['trip_assign_open'] = $daftarAbsensi['trip_assign_open'];
        $data['trip_assign_close'] = $daftarAbsensi['trip_assign_close'];

        //TABLE
        $data['occupancy_rate'] = $this->occupancyByTrasTable($param);

        return $data;
    }

    public function nullChart($type)
    {

        $label = array('0');
        $value = array(0);

        $data = Chartjs::build()
            ->name("NullChart")
            ->type($type)
            ->size(["width" => 40, "height" => 15])
            ->labels($label)
            ->datasets([
                [
                    "label" => "null",
                    "data" => $value,
                    'backgroundColor' => generateColor(0),
                ]
            ]);

        return $data;
    }

    public function totalKeterisianKursiChart($param, $classInfo, $seatAndClassBookingData)
    {

        $class_info = $classInfo;
        $book_seat = $seatAndClassBookingData->groupBy('trip_route_id')
            ->map(function ($group, $trip_route_id) {
                return (object)[
                    'trip_route_id' => $trip_route_id,
                    'passengger' => $group->count()
                ];
            });

        $bookedSeat = 0;
        $totalSeat = 0;

        foreach ($param['trip_route_grouped'] as $value) {
            foreach ($book_seat as $item) {
                if (in_array($item->trip_route_id, $value->route)) {
                    $bookedSeat = $bookedSeat + $item->passengger;
                }
            }

            foreach ($class_info as $item) {
                if (in_array($item->trip_route_id, $value->route)) {
                    $total_days = $item->total_seat * $item->days_active;
                    $totalSeat = $totalSeat + $total_days;
                }
            }
        }

        $leftSeat = $totalSeat - $bookedSeat;
        if ($leftSeat < 0) $leftSeat = 0;

        $percentage = 0;
        if ($bookedSeat != 0 && $totalSeat != 0) $percentage = ($bookedSeat * 100 / $totalSeat);
        $percentage = number_format($percentage, 2, '.', '');
        $data['percentage'] = "{$percentage}%";

        $data['description'] = "{$bookedSeat} Seat / {$totalSeat} Max Seat";

        $data['chart'] = Chartjs::build()
            ->name("TotalKeterisianKursi")
            ->type("doughnut")
            ->size(["width" => 400, "height" => 150])
            ->labels(['Seat Terjual', 'Sisa Seat'])
            ->datasets([
                [
                    'backgroundColor' => [generateColor(1), generateColor(0)],
                    "data" => [$bookedSeat, $leftSeat],
                ]
            ])->options([
                'plugins' => [
                    'legend' => false
                ]
            ]);

        return $data;
    }

    public function occupancyByRouteChart($param, $classInfo, $seatAndClassBookingData)
    {
        $class_info = $classInfo;
        $book_seat = $seatAndClassBookingData->groupBy('trip_route_id')
            ->map(function ($group, $trip_route_id) {
                return (object)[
                    'trip_route_id' => $trip_route_id,
                    'passengger' => $group->count()
                ];
            });

        foreach ($param['trip_route_grouped'] as $value) {
            $value->passengger = 0;
            $value->max_seat = 0;
            foreach ($book_seat as $item) {
                if (in_array($item->trip_route_id, $value->route)) {
                    $value->passengger = $value->passengger + $item->passengger;
                }
            }

            foreach ($class_info as $item) {
                if (in_array($item->trip_route_id, $value->route)) {
                    $total_days = $item->total_seat * $item->days_active;
                    $value->max_seat = $value->max_seat + $total_days;
                }
            }
        }

        $label = array();
        $max_seat = array();
        $seat = array();


        foreach ($param['trip_route_grouped'] as $value) {
            array_push($label, $value->name);
            array_push($max_seat, $value->max_seat);
            array_push($seat, $value->passengger);
        }

        $data['bar_chart'] = Chartjs::build()
            ->name("OccupancyByRouteBar")
            ->type("bar")
            ->size(["width" => 400, "height" => 150])
            ->labels($label)
            ->datasets([
                [
                    "label" => "Seat Max",
                    "data" => $max_seat,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
                ],
                [
                    "label" => "Seat Terjual",
                    "data" => $seat,
                    'backgroundColor' => generateColor(1),
                    'stack' => 'Stack 1',
                ]
            ]);

        if (count($param['trip_route_grouped']) > 0) {
            foreach ($param['trip_route_grouped'] as $key => $value) {
                $leftSeat = $value->max_seat - $value->passengger;
                if ($leftSeat < 0) $leftSeat = 0;

                $percentage = 0;
                if ($value->max_seat != 0 && $value->passengger != 0) $percentage = ($value->passengger * 100 / $value->max_seat);
                $percentage = number_format($percentage, 2, '.', '');
                $data['doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['doughnut_chart'][$key]['label'] = $value->name;
                $data['doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("OccupancyByClassDoughnut{$key}")
                    ->type("doughnut")
                    ->size(["width" => 400, "height" => 150])
                    ->labels(['Seat Terjual', 'Sisa Seat'])
                    ->datasets([
                        [
                            'backgroundColor' => [generateColor(1), generateColor(0)],
                            "data" => [$value->passengger, $leftSeat],
                        ]
                    ])->options([
                        'plugins' => [
                            'legend' => false
                        ]
                    ]);
            }
        } else {
            $data['doughnut_chart'][0]['percentage'] = "";
            $data['doughnut_chart'][0]['label'] = "";
            $data['doughnut_chart'][0]['chart'] = $this->nullChart('doughnut');
        }

        return $data;
    }

    public function occupancyByTrasTable($param)
    {
        $classInfo = Akap::getAkapClassInfoTable($param);

        $tras_seat = array();

        foreach ($classInfo as $value) {
            if (array_key_exists($value->tras_id, $tras_seat)) {
                $tras_seat[$value->tras_id]['max_seat'] = $tras_seat[$value->tras_id]['max_seat'] + $value->total_seat;
            } else {
                $tras_seat[$value->tras_id]['id'] = $value->tras_id;
                $tras_seat[$value->tras_id]['max_seat'] = $value->total_seat;
                $tras_seat[$value->tras_id]['trip'] = $value->trip;
                $tras_seat[$value->tras_id]['bus'] = $value->bus;
                $tras_seat[$value->tras_id]['fleet_reg_id'] = $value->fleet_registration_id;
                $tras_seat[$value->tras_id]['status'] = $value->status;
            }
        }

        ksort($tras_seat);

        $lengthDay = cal_days_in_month(CAL_GREGORIAN, $param['month'], $param['year']) - 1;

        foreach ($tras_seat as $key => $value) {
            for ($x = 0; $x <= $lengthDay; $x++) {
                $day = $x + 1;
                $tras_seat[$key]['data'][$day]['date'] = $day;
                $tras_seat[$key]['data'][$day]['max_seat'] = 0;
                $tras_seat[$key]['data'][$day]['seat_sale'] = 0;
                $tras_seat[$key]['data'][$day]['occupancy'] = 0;
            }
        }

        $busOff = Akap::getTemporaryOff($param);
        $busOn = Akap::getTemporaryOn($param);

        foreach ($busOff as $value) {
            $start = new DateTime($value->date);
            $end = new DateTime($value->date_finish);
            $end->modify('+1 day');
            $period = new DatePeriod($start,new DateInterval('P1D'),$end);
            $dates = [];
            foreach ($period as $valueX) {
                    $dates[] = $valueX->format('j');
            }
            $value->days = $dates;
        }

        foreach ($busOn as $value) {
            $start = new DateTime($value->date);
            $end = new DateTime($value->date_finish);
            $end->modify('+1 day');
            $period = new DatePeriod($start,new DateInterval('P1D'),$end);
            $dates = [];
            foreach ($period as $valueX) {
                $dates[] = $valueX->format('j');
            }
            $value->days = $dates;
        }

        $book_seat = Akap::getBookByTripAssign($param);
        foreach ($book_seat as $value) {
            if ($value->seat > 0) {
                foreach ($tras_seat as $keyA => $valueA) {
                    if ($valueA['id'] == $value->tras_id) {
                        foreach ($valueA['data'] as $keyB => $valueB) {
                            if ($valueB['date'] == $value->date) {

                                if ($valueA['status'] = 1) {
                                    $tras_seat[$keyA]['data'][$keyB]['max_seat'] = $valueA['max_seat'];
                                    foreach ($busOff as $valueC) {
                                        if ($valueC->fleet_registration_id == $valueA['fleet_reg_id']){
                                            if (in_array($valueB['date'], $valueC->days)) {
                                                $tras_seat[$keyA]['data'][$keyB]['max_seat'] = 0;
                                            } 
                                        }
                                    }
                                }

                                if ($valueA['status'] = 0) {
                                    $tras_seat[$keyA]['data'][$keyB]['max_seat'] = 0;
                                    foreach ($busOn as $valueC) {
                                        if ($valueC->fleet_registration_id == $valueA['fleet_reg_id']){
                                            if (in_array($valueB['date'], $valueC->days)) {
                                                $tras_seat[$keyA]['data'][$keyB]['max_seat'] = $valueA['max_seat'];
                                            } 
                                        }
                                    }
                                }

                                $tras_seat[$keyA]['data'][$keyB]['seat_sale'] = $value->seat;
                                if ($tras_seat[$keyA]['data'][$keyB]['max_seat'] == 0) {
                                    $occup = 0;
                                } else {
                                    $occup = ($tras_seat[$keyA]['data'][$keyB]['seat_sale'] / $tras_seat[$keyA]['data'][$keyB]['max_seat']) * 100;
                                }
                                
                                $occupformat = number_format($occup, 0, ',', ' ');
                                $tras_seat[$keyA]['data'][$keyB]['occupancy'] = $occupformat . "%";
                            }
                        }
                    }
                }
            }
        }

        // dd($tras_seat);


        return $tras_seat;
    }

    public function occupancyByBusChart($param, $classInfo)
    {
        $class_info = $classInfo;

        $book_seat = Akap::getBookByBus($param);

        // REFERENCE BY BOOK
        // foreach ($book_seat as $valueA) {
        //     $valueA->max_seat = 0;
        //     foreach ($class_info as $valueB) {
        //         if ($valueA->tras_id == $valueB->tras_id) {
        //             $valueA->max_seat = $valueA->max_seat + $valueB->total_seat;
        //         }
        //     }
        // }

        $bus_seat = array();

        foreach ($book_seat as $key => $value) {
            if (array_key_exists($value->name, $bus_seat)) {
                $bus_seat[$value->name]['passengger'] = $bus_seat[$value->name]['passengger'] + $value->passengger;
            } else {
                $bus_seat[$value->name]['name'] = $value->name;
                $bus_seat[$value->name]['passengger'] = $value->passengger;
                $bus_seat[$value->name]['max_seat'] = 0;
                $bus_seat[$value->name]['tras_id'] = $value->tras_id;
            }
        }

        // REFERENCE BY SCHADULE
        foreach ($bus_seat as $key => $valueA) {
            $bus_seat[$key]['trip_count'] = 0;
            $bus_seat[$key]['max_seat'] = 0;
            foreach ($class_info as $valueB) {
                if ($valueA['tras_id'] == $valueB->tras_id) {
                    $bus_seat[$key]['max_seat'] = $bus_seat[$key]['max_seat'] + ($valueB->total_seat * $valueB->days_active);
                    $bus_seat[$key]['trip_count'] = $valueB->days_active;
                }
            }
        }


        $label = array();
        $max_seat = array();
        $seat = array();


        foreach ($bus_seat as $value) {
            array_push($label, $value['name']);
            array_push($max_seat, $value['max_seat']);
            array_push($seat, $value['passengger']);
        }


        $data['bar_chart'] = Chartjs::build()
            ->name("OccupancyByBusBar")
            ->type("bar")
            ->size(["width" => 400, "height" => 150])
            ->labels($label)
            ->datasets([
                [
                    "label" => "Seat Max",
                    "data" => $max_seat,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
                ],
                [
                    "label" => "Seat Terjual",
                    "data" => $seat,
                    'backgroundColor' => generateColor(1),
                    'stack' => 'Stack 1',
                ]
            ]);


        if (count($bus_seat) > 0) {
            foreach ($bus_seat as $key => $value) {
                $leftSeat = $value['max_seat'] - $value['passengger'];
                if ($leftSeat < 0) $leftSeat = 0;

                $percentage = 0;
                if ($value['max_seat'] != 0 && $value['passengger'] != 0) $percentage = ($value['passengger'] * 100 / $value['max_seat']);
                $percentage = number_format($percentage, 2, '.', '');
                $class = str_replace(' ', '', $key);
                $class = str_replace("-", "", $class);
                $data['doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['doughnut_chart'][$key]['label'] = $value['name'];
                $data['doughnut_chart'][$key]['trip_count'] = $value['trip_count'];
                $data['doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("OccupancyByBusDoughnut{$class}")
                    ->type("doughnut")
                    ->size(["width" => 400, "height" => 150])
                    ->labels(['Seat Terjual', 'Sisa Seat'])
                    ->datasets([
                        [
                            'backgroundColor' => [generateColor(1), generateColor(0)],
                            "data" => [$value['passengger'], $leftSeat],
                        ]
                    ])->options([
                        'plugins' => [
                            'legend' => false
                        ]
                    ]);
            }
        } else {
            $data['doughnut_chart'][0]['percentage'] = "";
            $data['doughnut_chart'][0]['label'] = "";
            $data['doughnut_chart'][0]['trip_count'] = "";
            $data['doughnut_chart'][0]['chart'] = $this->nullChart('doughnut');
        }

        return $data;
    }

    public function occupancyByClassChart($param, $classInfo, $seatAndClassBookingData)
    {
        $class_info = $classInfo;
        $book_seat = $seatAndClassBookingData->groupBy('type')
            ->map(function ($group, $type) {
                return (object)[
                    'type' => $type,
                    'passengger' => $group->count()
                ];
            });


        foreach ($book_seat as $value) {
            $value->max_seat = 0;
            foreach ($class_info as $item) {
                if ($item->type == $value->type) {
                    $total_days = $item->total_seat * $item->days_active;
                    $value->max_seat = $value->max_seat + $total_days;
                }
            }
        }

        $label = array();
        $max_seat = array();
        $seat = array();


        foreach ($book_seat as $value) {
            array_push($label, $value->type);
            array_push($max_seat, $value->max_seat);
            array_push($seat, $value->passengger);
        }


        $data['bar_chart'] = Chartjs::build()
            ->name("OccupancyByClass")
            ->type("bar")
            ->size(["width" => 400, "height" => 150])
            ->labels($label)
            ->datasets([
                [
                    "label" => "Seat Max",
                    "data" => $max_seat,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
                ],
                [
                    "label" => "Seat Terjual",
                    "data" => $seat,
                    'backgroundColor' => generateColor(1),
                    'stack' => 'Stack 1',
                ]
            ]);


        if (count($book_seat) > 0) {
            foreach ($book_seat as $key => $value) {
                $leftSeat = $value->max_seat - $value->passengger;
                if ($leftSeat < 0) $leftSeat = 0;

                $percentage = 0;
                if ($value->max_seat != 0 && $value->passengger != 0) $percentage = ($value->passengger * 100 / $value->max_seat);
                $percentage = number_format($percentage, 2, '.', '');
                $class = str_replace(' ', '', $value->type);
                $class = str_replace("-", "", $class);
                $data['doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['doughnut_chart'][$key]['label'] = $value->type;
                $data['doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("OccupancyByRouteDoughnut{$class}")
                    ->type("doughnut")
                    ->size(["width" => 400, "height" => 150])
                    ->labels(['Seat Terjual', 'Sisa Seat'])
                    ->datasets([
                        [
                            'backgroundColor' => [generateColor(1), generateColor(0)],
                            "data" => [$value->passengger, $leftSeat],
                        ]
                    ])->options([
                        'plugins' => [
                            'legend' => false
                        ]
                    ]);
            }
        } else {
            $data['doughnut_chart'][0]['percentage'] = "";
            $data['doughnut_chart'][0]['label'] = "";
            $data['doughnut_chart'][0]['chart'] = $this->nullChart('doughnut');
        }

        return $data;
    }

    public function dailyPassenggerChart($param, $seatAndClassBookingData)
    {
        $daysInMonth = Carbon::now()->month($param['month'])->daysInMonth;
        $keys = range(1, $daysInMonth);

        if (empty($param['trip_route_group'])) { 
            $daily_passengger = $seatAndClassBookingData->groupBy('date')->map(function ($group) {
                return $group->count();
            });

            $values = collect($keys)->map(fn($day) => $daily_passengger->get($day, 0))->toArray();

            $data = Chartjs::build()
                ->name("DailyPassengger")
                ->type("line")
                ->size(["width" => 400, "height" => 150])
                ->labels($keys)
                ->datasets([
                    [
                        "label" => "Semua Rute",
                        "data" => $values,
                        'borderColor' => generateColor(1),
                        'stack' => 'Stack 0',
                        'fill' => false,
                        'pointBorderWidth' => 4,
                    ]
                ]);
        } else {
            $trip_route_details = $param['trip_route_grouped'][0];
            $route_x_ids = explode(",", $trip_route_details->route_x);
            $route_y_ids = explode(",", $trip_route_details->route_y);

            $route_x_book = $seatAndClassBookingData->whereIn('trip_route_id', $route_x_ids)->groupBy('date')->map(function ($group) {
                return $group->count();
            });
            $route_y_book = $seatAndClassBookingData->whereIn('trip_route_id', $route_y_ids)->groupBy('date')->map(function ($group) {
                return $group->count();
            });

            $values_x = collect($keys)->map(fn($day) => $route_x_book->get($day, 0))->toArray();
            $values_y = collect($keys)->map(fn($day) => $route_y_book->get($day, 0))->toArray();

            $data = Chartjs::build()
                ->name("DailyPassengger")
                ->type("line")
                ->size(["width" => 400, "height" => 150])
                ->labels($keys)
                ->datasets([
                    [
                        "label" => $trip_route_details->name_x,
                        "data" => $values_x,
                        'borderColor' => generateColor(1),
                        'stack' => 'Stack 0',
                        'fill' => false,
                        'pointBorderWidth' => 4,
                    ],
                    [
                        "label" => $trip_route_details->name_y,
                        "data" => $values_y,
                        'borderColor' => generateColor(6),
                        'stack' => 'Stack 1',
                        'fill' => false,
                        'pointBorderWidth' => 4,
                    ]
                ]);
        }

        return $data;
    }

    public function perbandinganBulanLaluChart($param)
    {
        $current_month_param = $param;
        $current_month = $param['month'];
        $current_year = $param['year'];
        
        if ($param['month'] == 1) {
            $last_month = 12;
            $last_year = $param['year'] - 1;
        } else {
            $last_month = $param['month'] - 1;
            $last_year = $param['year'];
        }
        $last_month_param = array_merge($param, ['month' => $last_month, 'year' => $last_year]);

        $current_month_book = Akap::getDailyPassengerCounts($current_month_param);
        $current_month_summary = Akap::getMonthlyIncomeAndSeats($current_month_param);

        $data['current_month']['month'] = date("F", mktime(0, 0, 0, $current_month, 10));
        $data['current_month']['income'] = Number::currency($current_month_summary->price ?? 0, 'IDR');
        $data['current_month']['seat'] = $current_month_summary->seat ?? 0;

        $last_month_book = Akap::getDailyPassengerCounts($last_month_param);
        $last_month_summary = Akap::getMonthlyIncomeAndSeats($last_month_param);

        $data['last_month']['month'] = date("F", mktime(0, 0, 0, $last_month, 10));
        $data['last_month']['income'] = Number::currency($last_month_summary->price ?? 0, 'IDR');
        $data['last_month']['seat'] = $last_month_summary->seat ?? 0;

        $keys_a = $current_month_book->keys()->toArray();
        $keys_b = $last_month_book->keys()->toArray();
        $keys = count($keys_a) >= count($keys_b) ? $keys_a : $keys_b;
        
        $values_current = $current_month_book->values()->toArray();
        $values_last = $last_month_book->values()->toArray();

        $data['chart'] = Chartjs::build()
            ->name("PerbandinganBulanLalu")
            ->type("line")
            ->size(["width" => 400, "height" => 150])
            ->labels($keys)
            ->datasets([
                [
                    "label" => "{$current_month} - {$current_year}",
                    "data" => $values_current,
                    'borderColor' => generateColor(1),
                    'stack' => 'Stack 0',
                    'fill' => false,
                    'pointBorderWidth' => 4,
                ],
                [
                    "label" => "{$last_month} - {$last_year}",
                    "data" => $values_last,
                    'borderColor' => generateColor(6),
                    'stack' => 'Stack 1',
                    'fill' => false,
                    'pointBorderWidth' => 4,
                ]
            ]);

        return $data;
    }

    public function daftarAbsensiBus($param)
    {

        $daftar_absensi_bus['trip_assign_open'] = Akap::getTripAssignOpen($param);
        foreach ($daftar_absensi_bus['trip_assign_open'] as $key => $item) {
            $date = DateTime::createFromFormat("Y-m-d", $item->date)->format("d");
            $date_finish = DateTime::createFromFormat("Y-m-d", $item->date_finish)->format("d");
            $date_count = ($date_finish - $date) + 1;
            $daftar_absensi_bus['trip_assign_open'][$key]->date_count = $date_count;
        }

        $daftar_absensi_bus['trip_assign_close'] = Akap::getTripAssignClose($param);
        foreach ($daftar_absensi_bus['trip_assign_close'] as $key => $item) {
            $date = DateTime::createFromFormat("Y-m-d", $item->date)->format("d");
            $date_finish = DateTime::createFromFormat("Y-m-d", $item->date_finish)->format("d");
            $date_count = ($date_finish - $date) + 1;
            $daftar_absensi_bus['trip_assign_close'][$key]->date_count = $date_count;
        }

        return $daftar_absensi_bus;
    }

    public function ticketingSupportChart($param)
    {
        $akap = Akap::getTicketingSupport($param);

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

    public function perbandinganTitikNaik($param, $seatAndClassBookingData)
    {
        $bookingPoints = $seatAndClassBookingData;

        //DEPARTURE
        $departure = $bookingPoints->groupBy('pickup_trip_location')
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($count) => $count);

        $total_departure = $departure->sum();
        $departureArr = [];
        foreach ($departure as $key => $value) {
            $departureArr[$key]['name'] = $key;
            $departureArr[$key]['value'] = $value;
            $percentage = ($total_departure > 0) ? ($value * 100 / $total_departure) : 0;
            $departureArr[$key]['percentage'] = number_format($percentage, 2, '.', '');
        }

        $departure_label = $departure->keys()->toArray();
        $departure_value = $departure->values()->toArray();

        //ARRIVAL
        $arrival = $bookingPoints->groupBy('drop_trip_location')
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($count) => $count);

        $total_arrival = $arrival->sum();
        $arrivalArr = [];
        foreach ($arrival as $key => $value) {
            $arrivalArr[$key]['name'] = $key;
            $arrivalArr[$key]['value'] = $value;
            $percentage = ($total_arrival > 0) ? ($value * 100 / $total_arrival) : 0;
            $arrivalArr[$key]['percentage'] = number_format($percentage, 2, '.', '');
        }

        $arrival_label = $arrival->keys()->toArray();
        $arrival_value = $arrival->values()->toArray();

        //BAR CHART
        $data['departure_bar_chart'] = Chartjs::build()
            ->name("departureBarChart")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 400])
            ->labels($departure_label)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $departure_value,
                    'backgroundColor' => generateColor(1),
                ]
            ]);

        $data['arrival_bar_chart'] = Chartjs::build()
            ->name("arrivalBarChart")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 400])
            ->labels($arrival_label)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $arrival_value,
                    'backgroundColor' => generateColor(2),
                ]
            ]);

        //DOUGHNUT CHART
        if (count($departureArr) > 0) {
            foreach ($departureArr as $key => $value) {
                $percentage = $value['percentage'];
                $label = $value['name'];
                $class = str_replace(' ', '', $label);
                $leftPassengger = $total_departure - $value['value'];
                $data['departure_doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['departure_doughnut_chart'][$key]['label'] = $label;
                $data['departure_doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("DepartureDoughnut{$class}")
                    ->type("doughnut")
                    ->size(["width" => 400, "height" => 150])
                    ->labels(['Penumpang', ''])
                    ->datasets([
                        [
                            'backgroundColor' => [generateColor(1), generateColor(0)],
                            "data" => [$value['value'], $leftPassengger],
                        ]
                    ])->options([
                        'plugins' => [
                            'legend' => false
                        ]
                    ]);
            }
        } else {
            $data['departure_doughnut_chart'][0]['percentage'] = "";
            $data['departure_doughnut_chart'][0]['label'] = "";
            $data['departure_doughnut_chart'][0]['chart'] = $this->nullChart('doughnut');
        }

        if (count($arrivalArr) > 0) {
            foreach ($arrivalArr as $key => $value) {
                $percentage = $value['percentage'];
                $label = $value['name'];
                $class = str_replace(' ', '', $label);
                $leftPassengger = $total_arrival - $value['value'];
                $data['arrival_doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['arrival_doughnut_chart'][$key]['label'] = $label;
                $data['arrival_doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("ArrivalDoughnut{$class}")
                    ->type('doughnut')
                    ->size(["width" => 400, "height" => 150])
                    ->labels(['Penumpang', ''])
                    ->datasets([
                        [
                            'backgroundColor' => [generateColor(2), generateColor(0)],
                            "data" => [$value['value'], $leftPassengger],
                        ]
                    ])->options([
                        'plugins' => [
                            'legend' => false
                        ]
                    ]);
            }
        } else {
            $data['arrival_doughnut_chart'][0]['percentage'] = "";
            $data['arrival_doughnut_chart'][0]['label'] = "";
            $data['arrival_doughnut_chart'][0]['chart'] = $this->nullChart('doughnut');
        }

        return $data;
    }

    public function classInfo($param)
    {
        $classInfo = Akap::getAkapClassInfoList($param);

        $totalDays = Carbon::now()->month($param['month'])->daysInMonth;

        foreach ($classInfo as $value) {
            if ($value->status == 1) {
                $value->days_active = $totalDays;
            } else {
                $value->days_active = 0;
            }
        }

        // REFERENCE BY TEMPORARY ON & OFF

        $busOff = Akap::getTemporaryOff($param);
        $busOn = Akap::getTemporaryOn($param);

        foreach ($busOff as $value) {
            $dateFrom=Carbon::parse($value->date);
            $dateTo=Carbon::parse($value->date_finish);
            $value->count_days = $dateFrom->diffInDays($dateTo) + 1;

            foreach ($classInfo as $valueX) {
                if ($value->fleet_registration_id == $valueX->fleet_registration_id) {
                    $valueX->days_active = $valueX->days_active - $value->count_days;
                }
            }
        }

        foreach ($busOn as $value) {
            $dateFrom=Carbon::parse($value->date);
            $dateTo=Carbon::parse($value->date_finish);
            $value->count_days = $dateFrom->diffInDays($dateTo) + 1;

            foreach ($classInfo as $valueX) {
                if ($value->fleet_registration_id == $valueX->fleet_registration_id) {
                    $valueX->days_active = $valueX->days_active + $value->count_days;
                }
            }
        }

        
        // REFERENCE BY BOOK

        // $temp_assign = array();

        // $book_seat = Akap::getBookByTripAssign($param);
        // foreach ($book_seat as $value) {
        //     if ($value->seat > 0) {
        //         if (isset($temp_assign[$value->tras_id])) {
        //             $temp_assign[$value->tras_id] = $temp_assign[$value->tras_id] + 1;
        //         } else {
        //             $temp_assign[$value->tras_id] = 1;
        //         }
        //     }
        // }

        // foreach ($classInfo as $value) {
        //     if (isset($temp_assign[$value->tras_id])) {
        //         $value->days_active = $temp_assign[$value->tras_id];
        //     }
        // }

        return $classInfo;
    }
}