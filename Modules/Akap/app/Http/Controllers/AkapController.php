<?php

namespace Modules\Akap\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Akap;
use Illuminate\Support\Number;

class AkapController extends Controller
{
    public function index(Request $request)
    {

        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');
        $start_point = null;
        $end_point = null;

        if ($request->has('trip')) {
            $trip = $request->input('trip');
            $trip = explode("N", $trip);
            $start_point = $trip[0];
            $end_point = $trip[1];
        }

        $paramTripAssign = [
            'start_point' => $start_point,
            'end_point' => $end_point,
        ];

        $tripAssign = json_decode(json_encode(Akap::getTripAssign($paramTripAssign)), true);

        $paramIncome = [
            'month' => $month,
            'year' => $year,
            'tras_id' => $tripAssign,
        ];

        $data['income'] = Number::currency(Akap::getIncome($paramIncome), 'IDR');

        $data['route_group'] = Akap::getTripRouteGroup();
        $data['title'] = 'REPORT AKAP';
        return view('akap::index', $data);
    }
}
