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

class AkapMonthlyController extends Controller
{
    public function index(Request $request)
    {
        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');

        $trip_route_grouped = null;
        $trip_route_group = null;
        $trip_group = null;
        $trip_assign_group = null;
        $total_days = Carbon::now()->month($month)->daysInMonth;

        if ($request->has('trip')) {
            $trip = $request->input('trip');
            $trip_route_group = Akap::getTripRouteGroup($trip);
            $trip_route_grouped = Akap::getTripRouteGroup($trip);
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
                unset($trip_route_grouped[0]->name_x);
                unset($trip_route_grouped[0]->name_y);
                unset($trip_route_grouped[0]->route_x);
                unset($trip_route_grouped[0]->route_y);
            }
        } else {
            $trip_route_grouped = Akap::getTripRouteGroup();
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
                unset($value->name_x);
                unset($value->name_y);
                unset($value->route_x);
                unset($value->route_y);
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
        $param['trip_group'] = join(',', array_column($param['trip_group'], 'trip_id'));
        $param['trip_group'] = explode(",", $param['trip_group']);
        $param['trip_assign_group'] = Akap::getTripAssignGroup($param)->toArray();
        $param['trip_assign_group'] = join(',', array_column($param['trip_assign_group'], 'id'));
        $param['trip_assign_group'] = explode(",", $param['trip_assign_group']);

        //CHART DATA
        $data['occupancy_by_route_bar'] = $this->occupancyByRouteChart($param,$classInfo)['bar_chart'];
        $data['occupancy_by_route_doughnut'] = $this->occupancyByRouteChart($param,$classInfo)['doughnut_chart'];
        $data['occupancy_by_bus_bar'] = $this->occupancyByBusChart($param,$classInfo)['bar_chart'];
        $data['occupancy_by_bus_doughnut'] = $this->occupancyByBusChart($param,$classInfo)['doughnut_chart'];
        $data['occupancy_by_class_bar'] = $this->occupancyByClassChart($param,$classInfo)['bar_chart'];
        $data['occupancy_by_class_doughnut'] = $this->occupancyByClassChart($param,$classInfo)['doughnut_chart'];
        $data['ticketing_support_bar'] = $this->ticketingSupportChart($param)['bar_chart'];
        $data['ticketing_support_pie_chart'] = $this->ticketingSupportChart($param)['pie_chart'];
        $data['daily_passengger'] = $this->dailyPassenggerChart($param);
        $data['total_keterisian_kursi'] = $this->totalKeterisianKursiChart($param,$classInfo);
        $data['perbandingan_bulan_lalu_chart'] = $this->perbandinganBulanLaluChart($param)['chart'];
        $data['perbandingan_bulan_lalu_current_month'] = $this->perbandinganBulanLaluChart($param)['current_month'];
        $data['perbandingan_bulan_lalu_last_month'] = $this->perbandinganBulanLaluChart($param)['last_month'];
        $data['perbandingan_titik_naik_departure_bar_chart'] = $this->perbandinganTitikNaik($param)['departure_bar_chart'];
        $data['perbandingan_titik_naik_arrival_bar_chart'] = $this->perbandinganTitikNaik($param)['arrival_bar_chart'];
        $data['perbandingan_titik_naik_departure_doughnut_chart'] = $this->perbandinganTitikNaik($param)['departure_doughnut_chart'];
        $data['perbandingan_titik_naik_arrival_doughnut_chart'] = $this->perbandinganTitikNaik($param)['arrival_doughnut_chart'];


        //DATA
        $data['income'] = Number::currency(Akap::getIncome($param), 'IDR');
        $data['target'] = $target;
        $data['route_group'] = Akap::getTripRouteGroup();
        $data['title'] = 'REPORT AKAP BULANAN';
        $data['trip_assign_open'] = $this->daftarAbsensiBus($param)['trip_assign_open'];
        $data['trip_assign_close'] = $this->daftarAbsensiBus($param)['trip_assign_close'];

        //TABLE
        $data['occupancy_rate'] = $this->occupancyByTrasTable($param);

        return view('akap::monthly', $data);
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

    public function totalKeterisianKursiChart($param, $classInfo)
    {

        $class_info = $classInfo;
        $book_seat = Akap::getBookSeat($param);

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

    public function occupancyByRouteChart($param, $classInfo)
    {
        $class_info = $classInfo;
        $book_seat = Akap::getBookSeat($param);

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
                $tras_detail = Akap::getTripAssignDetail($value->tras_id);
                $tras_seat[$value->tras_id]['trip'] = $tras_detail[0]->trip;
                $tras_seat[$value->tras_id]['bus'] = $tras_detail[0]->bus;
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

    public function occupancyByClassChart($param, $classInfo)
    {
        $class_info = $classInfo;
        $book_seat = Akap::getBookByClass($param);


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

    public function dailyPassenggerChart($param)
    {

        if ($param['trip_route_group'] == null) {
            $daily_passengger = Akap::getDailyPassengger($param);
            $keys = $daily_passengger->keys()->toArray();
            $values = $daily_passengger->values()->toArray();

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
            $name = $param['trip_route_grouped'][0]->name;
            $trip_route_group = Akap::getTripRouteGroupByName($name);
            $route_x = array();
            $route_y = array();

            $rx = explode(",", $trip_route_group[0]->route_x);
            foreach ($rx as $val) {
                array_push($route_x, $val);
            }

            $ry = explode(",", $trip_route_group[0]->route_y);
            foreach ($ry as $val) {
                array_push($route_y, $val);
            }

            $route_x_book = Akap::getDailyPassenggerByTrip($param, $route_x);
            $route_y_book = Akap::getDailyPassenggerByTrip($param, $route_y);

            $keys = $route_x_book->keys()->toArray();
            $values_x = $route_x_book->values()->toArray();
            $values_y = $route_y_book->values()->toArray();

            $data = Chartjs::build()
                ->name("DailyPassengger")
                ->type("line")
                ->size(["width" => 400, "height" => 150])
                ->labels($keys)
                ->datasets([
                    [
                        "label" => $trip_route_group[0]->name_x,
                        "data" => $values_x,
                        'borderColor' => generateColor(1),
                        'stack' => 'Stack 0',
                        'fill' => false,
                        'pointBorderWidth' => 4,
                    ],
                    [
                        "label" => $trip_route_group[0]->name_y,
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
        $current_month = $param['month'];
        $current_year = $param['year'];
        $last_month = 0;
        $last_year = 0;

        if ($param['month'] == 1) {
            $last_month = 12;
            $last_year = $param['year'] - 1;
        } else {
            $last_month = $param['month'] - 1;
            $last_year = $param['year'];
        }

        $current_month_book = Akap::getDailyPassengger($param);
        $current_month_income = Akap::getDailyPassenggerIncome($param);

        $data['current_month']['month'] = date("F", mktime(0, 0, 0, $param['month'], 10));
        $data['current_month']['income'] = number_format($current_month_income[0]->price, 0, '.', ',');
        $data['current_month']['seat'] = $current_month_income[0]->seat;

        $param['month'] = $last_month;
        $param['year'] = $last_year;
        $last_month_book = Akap::getDailyPassengger($param);
        $last_month_income = Akap::getDailyPassenggerIncome($param);

        $data['last_month']['month'] = date("F", mktime(0, 0, 0, $param['month'], 10));
        $data['last_month']['income'] = number_format($last_month_income[0]->price, 0, '.', ',');
        $data['last_month']['seat'] = $last_month_income[0]->seat;


        $keys = array();
        $keys_a = $current_month_book->keys()->toArray();
        $keys_b = $last_month_book->keys()->toArray();

        if (count($keys_a) >= count($keys_b)) {
            $keys = $keys_a;
        } else {
            $keys = $keys_b;
        }

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

    public function perbandinganTitikNaik($param)
    {
        //DEPARTURE
        $departure = Akap::getTicketDeparturePointGroup($param);

        $total_departure = 0;
        foreach ($departure as $value) {
            $total_departure = $total_departure + $value;
        }

        $departureArr = array();

        foreach ($departure as $key => $value) {
            $departureArr[$key]['name'] = $key;
            $departureArr[$key]['value'] = $value;
            $percentage = ($value * 100 / $total_departure);
            $departureArr[$key]['percentage'] = number_format($percentage, 2, '.', '');
        }

        $departure_label = array();
        $departure_value = array();

        foreach ($departureArr as $value) {
            $departure_label[] = $value['name'];
            $departure_value[] = $value['value'];
        }

        //ARRIVAL
        $arrival = Akap::getTicketArrivalPointGroup($param);

        $total_arrival = 0;
        foreach ($arrival as $value) {
            $total_arrival = $total_arrival + $value;
        }

        $arrivalArr = array();

        foreach ($arrival as $key => $value) {
            $arrivalArr[$key]['name'] = $key;
            $arrivalArr[$key]['value'] = $value;
            $percentage = ($value * 100 / $total_arrival);
            $arrivalArr[$key]['percentage'] = number_format($percentage, 2, '.', '');
        }

        $arrival_label = array();
        $arrival_value = array();

        foreach ($arrivalArr as $value) {
            $arrival_label[] = $value['name'];
            $arrival_value[] = $value['value'];
        }

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
                $leftPassengger = $total_departure - $value['value'];
                $data['arrival_doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['arrival_doughnut_chart'][$key]['label'] = $label;
                $data['arrival_doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("ArrivalDoughnut{$class}")
                    ->type("doughnut")
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
