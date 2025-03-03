<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akap;
use Illuminate\Support\Number;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use DateTime;
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
        $param['trip_group'] = Akap::getTripGroup($param)->toArray();
        $param['trip_group'] = join(',', array_column($param['trip_group'], 'trip_id'));
        $param['trip_group'] = explode(",", $param['trip_group']);
        $param['trip_assign_group'] = Akap::getTripAssignGroup($param)->toArray();
        $param['trip_assign_group'] = join(',', array_column($param['trip_assign_group'], 'id'));
        $param['trip_assign_group'] = explode(",", $param['trip_assign_group']);

        //CHART DATA
        $data['occupancy_by_route_bar'] = $this->occupancyByRouteChart($param)['bar_chart'];
        $data['occupancy_by_route_doughnut'] = $this->occupancyByRouteChart($param)['doughnut_chart'];
        $data['occupancy_by_class_bar'] = $this->occupancyByClassChart($param)['bar_chart'];
        $data['occupancy_by_class_doughnut'] = $this->occupancyByClassChart($param)['doughnut_chart'];
        $data['ticketing_support'] = $this->ticketingSupportChart($param);
        $data['daily_passengger'] = $this->dailyPassenggerChart($param);
        $data['total_keterisian_kursi'] = $this->totalKeterisianKursiChart($param);
        $data['perbandingan_bulan_lalu'] = $this->perbandinganBulanLaluChart($param);

        //DATA
        $data['income'] = Number::currency(Akap::getIncome($param), 'IDR');
        $data['route_group'] = Akap::getTripRouteGroup();
        $data['title'] = 'REPORT AKAP BULANAN';
        $data['trip_assign_open'] = $this->daftarAbsensiBus($param)['trip_assign_open'];
        $data['trip_assign_close'] = $this->daftarAbsensiBus($param)['trip_assign_close'];

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

    public function totalKeterisianKursiChart($param)
    {

        $class_info = $this->classInfo($param);
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

    public function occupancyByRouteChart($param)
    {
        $class_info = $this->classInfo($param);
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

        // dd($data['doughnut_chart']);



        return $data;
    }

    public function occupancyByClassChart($param)
    {
        $class_info = $this->classInfo($param);
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

                $data['doughnut_chart'][$key]['percentage'] = "{$percentage}%";
                $data['doughnut_chart'][$key]['label'] = $value->type;
                $data['doughnut_chart'][$key]['chart'] = Chartjs::build()
                    ->name("OccupancyByRouteDoughnut{$key}")
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

        // dd($data['doughnut_chart']);



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

        $param['month'] = $last_month;
        $param['year'] = $last_year;
        $last_month_book = Akap::getDailyPassengger($param);


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

        $data = Chartjs::build()
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

        // dd($akap);


        $onlineLabel = "Online";
        $onlineValue = 0;

        $agenLabel = "Agen";
        $agenValue = 0;

        $kpLabel = "KP";
        $kpValue = 0;

        $listAgen = array('no-reply@traveloka.com', 'ybc@gmail.com');


        foreach ($akap as $value) {
            if (in_array($value->booker, $listAgen)) {
                $agenValue = $agenValue + $value->passengger;
            } else {
                if (strstr(strtolower($value->booker), 'kantorperwakilan')) {
                    $kpValue = $kpValue + $value->passengger;
                } else {
                    $onlineValue = $onlineValue + $value->passengger;
                }
            }
        }

        $label = array($onlineLabel, $agenLabel, $kpLabel);
        $value = array($onlineValue, $agenValue, $kpValue);
        $color = array(generateColor(0), generateColor(1), generateColor(2));
        $data = Chartjs::build()
            ->name("TicketSupport")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 150])
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

        return $data;
    }

    public function classInfo($param)
    {

        $classInfo = Akap::getAkapClassInfoList($param);


        foreach ($classInfo as $value) {
            $value->days_active = 0;
        }

        $temp_assign = array();

        $book_seat = Akap::getBookByTripAssign($param);
        foreach ($book_seat as $value) {
            if ($value->seat > 0) {
                if (isset($temp_assign[$value->tras_id])) {
                    $temp_assign[$value->tras_id] = $temp_assign[$value->tras_id] + 1;
                } else {
                    $temp_assign[$value->tras_id] = 1;
                }
            }
        }

        foreach ($classInfo as $value) {
            if (isset($temp_assign[$value->tras_id])) {
                $value->days_active = $temp_assign[$value->tras_id];
            }
        }


        return $classInfo;
    }
}
