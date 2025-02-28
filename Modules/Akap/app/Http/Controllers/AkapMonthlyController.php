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
        $data['occupancy_by_route'] = ($request->has('trip')) ? $this->nullChart() : $this->occupancyByRouteChart($param);
        $data['occupancy_by_class'] = $this->occupancyByClassChart($param);
        $data['ticketing_support'] = $this->ticketingSupportChart($param);
        $data['daily_passengger'] = $this->dailyPassenggerChart($param);

        //DATA
        $data['income'] = Number::currency(Akap::getIncome($param), 'IDR');
        $data['route_group'] = Akap::getTripRouteGroup();
        $data['title'] = 'REPORT AKAP BULANAN';
        $data['trip_assign_open'] = $this->daftarAbsensiBus($param)['trip_assign_open'];
        $data['trip_assign_close'] = $this->daftarAbsensiBus($param)['trip_assign_close'];

        return view('akap::monthly', $data);
    }

    public function nullChart()
    {

        $label = array('0');
        $value = array(0);

        $data = Chartjs::build()
            ->name("NullChart")
            ->type("bar")
            ->size(["width" => 400, "height" => 150])
            ->labels($label)
            ->datasets([
                [
                    "label" => "null",
                    "data" => $value,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
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

        $data = Chartjs::build()
            ->name("OccupancyByRoute")
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


        $data = Chartjs::build()
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

        return $data;
    }

    public function dailyPassenggerChart($param)
    {
        $daily_passengger = Akap::getDailyPassengger($param);
        $keys = $daily_passengger->keys()->toArray();
        $values = $daily_passengger->values()->toArray();

        $data = Chartjs::build()
            ->name("DailyPassengger")
            ->type("bar")
            ->size(["width" => 400, "height" => 150])
            ->labels($keys)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $values,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',
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
        $data = Chartjs::build()
            ->name("TicketSupport")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 150])
            ->labels($label)
            ->datasets([
                [
                    "label" => "Penumpang",
                    "data" => $value,
                    'backgroundColor' => generateColor(0),
                    'stack' => 'Stack 0',

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
