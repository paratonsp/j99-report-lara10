<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akap;
use Illuminate\Support\Number;
use DateTime;
use Helper;
use Carbon\Carbon;

class AkapDailyController extends Controller
{
    public function index(Request $request)
    {
        $dateStart = ($request->has('dateStart')) ? $request->input('dateStart') : date('Y-m-d');
        $dateEnd = ($request->has('dateEnd')) ? $request->input('dateEnd') : date('Y-m-d');

        $daily_income = Akap::getDailyIncome($dateStart, $dateEnd);

        $trip_route_group = Akap::getTripRouteGroup();

        $mainIncome = 0;

        foreach ($trip_route_group as $value) {
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

            $value->income = 0;

            foreach ($daily_income as $item) {
                if (in_array($item->trip_route_id, $value->route)) {;
                    $value->income = $value->income + $item->price;
                }
            }

            $mainIncome = $mainIncome + $value->income;
            $value->income = Number::currency($value->income, 'IDR');
        }

        $data['mainIncome'] = Number::currency($mainIncome, 'IDR');
        $data['routeIncome'] = $trip_route_group;

        $data['title'] = 'REPORT AKAP HARIAN';
        return view('akap::daily', $data);
    }
}
